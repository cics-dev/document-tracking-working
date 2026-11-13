<?php

use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;

beforeEach(function () {
    $this->faqFile = tmpfile();
    config(['chatbot.additional_faq_path' => stream_get_meta_data($this->faqFile)['uri']]);
    fwrite($this->faqFile, '[]');
});

afterEach(function () {
    fclose($this->faqFile);
});

it('answers DTS definitions immediately without calling OpenAI', function ($question) {
    OpenAI::fake();
    $this->postJson('/chat/send', ['message' => $question])->assertOk()
        ->assertJsonPath('response', fn ($answer) => str_contains($answer, 'DTS stands for Document Tracking System.'));
    OpenAI::assertNotSent(Chat::class);
})->with(['What is DTS?', 'WHAT IS DTS!!!', ' what does DTS stand for? ', 'DTS']);

it('keeps DTS context when the optional FAQ file is missing or invalid', function ($contents) {
    if ($contents === null) {
        config(['chatbot.additional_faq_path' => stream_get_meta_data($this->faqFile)['uri'].'.missing']);
    } else {
        ftruncate($this->faqFile, 0);
        rewind($this->faqFile);
        fwrite($this->faqFile, $contents);
    }
    OpenAI::fake([CreateResponse::fake()]);
    $this->postJson('/chat/send', ['message' => 'Explain how this system helps our offices.'])->assertOk();
    OpenAI::assertSent(Chat::class, function ($method, $parameters) {
        expect($parameters['messages'][0]['role'])->toBe('developer')
            ->and($parameters['messages'][0]['content'])->toContain('DTS always means Document Tracking System', 'Do not invent', 'no live document')
            ->and($parameters['messages'][1]['content'])->toContain('What is DTS?')
            ->and($parameters['messages'][2])->toBe(['role' => 'user', 'content' => 'Explain how this system helps our offices.'])
            ->and($parameters['reasoning_effort'])->toBe('none')
            ->and($parameters['verbosity'])->toBe('low');

        return true;
    });
})->with([null, '{broken JSON', '{"unexpected":"object"}']);

it('loads custom question and answer FAQs without an API call', function () {
    ftruncate($this->faqFile, 0);
    rewind($this->faqFile);
    fwrite($this->faqFile, json_encode([['question' => 'Where can I get DTS help?', 'answer' => 'Contact your office coordinator.']]));
    OpenAI::fake();
    $this->postJson('/chat/send', ['message' => 'Where can I get DTS help?'])
        ->assertOk()->assertJsonPath('response', 'Contact your office coordinator.');
    OpenAI::assertNotSent(Chat::class);
});

it('includes legacy message FAQs as reference data rather than user turns', function () {
    ftruncate($this->faqFile, 0);
    rewind($this->faqFile);
    fwrite($this->faqFile, json_encode([['role' => 'assistant', 'content' => 'Legacy DTS help text.']]));
    OpenAI::fake([CreateResponse::fake()]);
    $this->postJson('/chat/send', ['message' => 'Please explain routing.'])->assertOk();
    OpenAI::assertSent(Chat::class, function ($method, $parameters) {
        expect($parameters['messages'])->toHaveCount(3)
            ->and($parameters['messages'][1]['content'])->toContain('Legacy DTS help text.');

        return true;
    });
});
