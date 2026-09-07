<?php

namespace Tests\Feature;

use App\Livewire\Documents\ListDocuments;
use App\Livewire\Notifications\SidebarNotifications;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\ExternalDocument;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationCountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationPollingTest extends TestCase
{
    use RefreshDatabase;

    public function test_polling_updates_badges_and_dispatches_notification_only_for_a_new_unread_document(): void
    {
        $role = Role::create(['role' => 'receiver', 'description' => 'Receiver']);
        $role->permissions()->attach(Permission::where('key', 'receive_documents')->firstOrFail());
        $office = Office::create(['name' => 'Receiving Office', 'abbreviation' => 'RO', 'office_type' => 'ADMIN']);
        $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $office->update(['head_id' => $user->id]);
        $sender = Office::create(['name' => 'Sender Office', 'abbreviation' => 'SO', 'office_type' => 'ADMIN']);
        $type = DocumentType::create(['name' => 'Memo', 'abbreviation' => 'MEMO']);
        $this->actingAs($user);

        $component = Livewire::test(SidebarNotifications::class)
            ->assertSet('unreadTotal', 0)
            ->assertSeeHtml('wire:poll.10s="refreshNotifications"')
            ->assertSeeHtml('toggleSound()');
        Document::create(['document_number' => 'MEMO-1', 'from_id' => $sender->id, 'to_id' => $office->id, 'document_type_id' => $type->id, 'subject' => 'New', 'content' => 'New', 'created_by' => $user->id, 'status' => 'Sent']);

        $component->call('refreshNotifications')
            ->assertSet('unreadReceived', 1)->assertSet('unreadTotal', 1)
            ->assertSet('showToast', true)->assertDispatched('new-document-notification');
    }

    public function test_external_notification_scope_is_controlled_by_permissions_not_position_names(): void
    {
        $receivingOffice = Office::create(['name' => 'Receiving Office', 'abbreviation' => 'RO', 'office_type' => 'ADMIN']);
        $otherOffice = Office::create(['name' => 'Other Office', 'abbreviation' => 'OO', 'office_type' => 'ADMIN']);

        $restrictedRole = Role::create(['role' => 'restricted-external', 'description' => 'Restricted External']);
        $restrictedRole->permissions()->attach(Permission::where('key', 'receive_external_documents')->firstOrFail());
        $restrictedUser = User::factory()->create([
            'role_id' => $restrictedRole->id,
            'office_id' => $receivingOffice->id,
            'position' => 'Staff',
        ]);

        $globalRole = Role::create(['role' => 'global-external', 'description' => 'Global External']);
        $globalRole->permissions()->attach(
            Permission::whereIn('key', ['receive_external_documents', 'view_all_documents'])->pluck('id')
        );
        $globalUser = User::factory()->create(['role_id' => $globalRole->id, 'office_id' => $receivingOffice->id]);

        foreach ([$receivingOffice, $otherOffice] as $index => $office) {
            ExternalDocument::create([
                'document_number' => 'EXT-'.($index + 1),
                'from' => 'External Sender',
                'to_id' => $office->id,
                'subject' => 'External document',
                'received_date' => now(),
                'file_url' => 'external/document.pdf',
                'file_type' => 'pdf',
            ]);
        }

        $service = app(NotificationCountService::class);

        $this->assertSame(1, $service->for($restrictedUser)['external']);
        $this->assertSame(2, $service->for($globalUser)['external']);
    }

    public function test_internal_notification_counts_use_the_received_and_sent_list_scopes(): void
    {
        $role = Role::create(['role' => 'document-user', 'description' => 'Document User']);
        $role->permissions()->attach(
            Permission::whereIn('key', ['receive_documents', 'send_documents'])->pluck('id')
        );
        $office = Office::create(['name' => 'User Office', 'abbreviation' => 'UO', 'office_type' => 'ADMIN']);
        $otherOffice = Office::create(['name' => 'Other Office', 'abbreviation' => 'OO', 'office_type' => 'ADMIN']);
        $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
        $otherUser = User::factory()->create(['office_id' => $otherOffice->id]);
        $type = DocumentType::create(['name' => 'Office Memo', 'abbreviation' => 'OM']);

        Document::create(['document_number' => 'OM-RECEIVED', 'from_id' => $otherOffice->id, 'to_id' => $office->id, 'document_type_id' => $type->id, 'subject' => 'Received', 'content' => 'Test', 'created_by' => $otherUser->id, 'status' => 'Sent', 'document_level' => 'Inter']);
        Document::create(['document_number' => 'OM-CREATED', 'from_id' => $otherOffice->id, 'to_id' => $otherOffice->id, 'document_type_id' => $type->id, 'subject' => 'Created by user', 'content' => 'Test', 'created_by' => $user->id, 'status' => 'Sent', 'document_level' => 'Inter']);
        Document::create(['document_number' => 'OM-OFFICE', 'from_id' => $office->id, 'to_id' => $otherOffice->id, 'document_type_id' => $type->id, 'subject' => 'Sent by office', 'content' => 'Test', 'created_by' => $otherUser->id, 'status' => 'Sent', 'document_level' => 'Inter']);
        Document::create(['document_number' => 'OM-UNRELATED', 'from_id' => $otherOffice->id, 'to_id' => $otherOffice->id, 'document_type_id' => $type->id, 'subject' => 'Unrelated', 'content' => 'Test', 'created_by' => $otherUser->id, 'status' => 'Sent', 'document_level' => 'Inter']);

        $counts = app(NotificationCountService::class)->for($user);

        $this->assertSame(1, $counts['received']);
        $this->assertSame(1, $counts['sent']);
        $this->assertSame(2, $counts['total']);

        $this->actingAs($user);
        $sentList = Livewire::test(ListDocuments::class, ['mode' => 'Sent'])
            ->assertSee('OM-CREATED')
            ->assertSee('OM-OFFICE');

        $this->assertSame(1, substr_count($sentList->html(), 'bg-blue-600 animate-pulse'));
    }
}
