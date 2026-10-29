<?php

use App\Models\Document;
use App\Support\DocumentHtml;

it('preserves supported Quill formatting and list metadata', function () {
    $html = '<p class="ql-align-center"><strong>Bold</strong><em>Italic</em><u>Underline</u><s>Strike</s><sup>2</sup><sub>3</sub><span style="color: rgb(230, 0, 0); background-color: yellow;">Color</span></p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>Item</li></ol>';
    $clean = DocumentHtml::sanitize($html);

    foreach (['ql-align-center', '<strong>Bold</strong>', '<em>Italic</em>', '<u>Underline</u>', '<s>Strike</s>', '<sup>2</sup>', '<sub>3</sub>', 'color:', 'background-color:', 'data-list="ordered"', 'ql-ui'] as $format) {
        expect($clean)->toContain($format);
    }
    expect(DocumentHtml::sanitize($clean))->toBe($clean);
});

it('removes executable HTML and embedded resources', function () {
    $html = '<p onclick="alert(1)" style="color: red; background-image: url(https://example.test/a);">Safe<script>alert(1)</script><img src=x onerror="alert(1)"><iframe src="https://example.test"></iframe><a href="javascript:alert(1)">link</a></p><svg onload="alert(1)"></svg>';
    $clean = DocumentHtml::sanitize($html);

    expect($clean)->toContain('Safe', 'link', 'color:');
    foreach (['onclick', 'onerror', '<script', '<img', '<iframe', 'javascript:', '<svg', 'background-image', 'https://'] as $unsafe) {
        expect($clean)->not->toContain($unsafe);
    }
});

it('sanitizes new writes and legacy database content without rewriting it on read', function () {
    $document = new Document;
    $document->content = '<p onclick="alert(1)"><strong>Saved</strong></p>';
    expect($document->getAttributes()['content'])->toBe('<p><strong>Saved</strong></p>');

    $legacy = '<p>Legacy<img src=x onerror="alert(1)"></p>';
    $document->setRawAttributes(['content' => $legacy]);
    expect($document->content)->toBe('<p>Legacy</p>');
    expect($document->getAttributes()['content'])->toBe($legacy);
    expect(DocumentHtml::sanitize(null))->toBe('');
});
