<?php

namespace Tests\Feature;

use App\Livewire\Notifications\SidebarNotifications;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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
}
