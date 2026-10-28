<?php

use App\Models\Document;
use App\Support\DocumentHtml;

it('preserves the supported Quill formatting', function () {
    $html = '<p class="ql-align-center"><strong>Bold</strong><em>Italic</em><u>Underline</u><s>Strike</s><sup>2</sup><sub>3</sub><span style="color: rgb(230, 0, 0);">Color</span></p><ol><li data-list="ordered"><span class="ql-ui" contenteditable="false"></span>Item</li></ol>';
    $clean = DocumentHtml::sanitize($html);

    foreach (['ql-align-center', '<strong>Bold</strong>', '<em>Italic</em>', '<u>Underline</u>', '<s>Strike</s>', '<sup>2</sup>', '<sub>3</sub>', 'color:', 'data-list="ordered"', 'ql-ui'] as $format) {
        expect($clean)->toContain($format);
    }
});

it('removes executable markup and resource loading while retaining text', function () {
    $html = '<p onclick="alert(1)" style="color: red; background-image: url(https://example.com/a);">Safe<script>alert(1)</script><img src=x onerror="alert(1)"><iframe src="https://example.com"></iframe><a href="javascript:alert(1)">link</a></p><svg onload="alert(1)"></svg>';
    $clean = DocumentHtml::sanitize($html);

    expect($clean)->toContain('Safe', 'link', 'color:');
    foreach (['onclick', 'onerror', '<script', '<img', '<iframe', 'javascript:', '<svg', 'background-image', 'https://'] as $unsafe) {
        expect($clean)->not->toContain($unsafe);
    }
    expect(DocumentHtml::sanitize($clean))->toBe($clean);
});

it('sanitizes both new content and legacy database values', function () {
    $document = new Document;
    $document->content = '<p onclick="alert(1)"><strong>Saved</strong></p>';
    expect($document->getAttributes()['content'])->toBe('<p><strong>Saved</strong></p>');

    $document->setRawAttributes(['content' => '<p>Legacy<img src=x onerror="alert(1)"></p>']);
    expect($document->content)->toBe('<p>Legacy</p>');
    expect(DocumentHtml::sanitize(null))->toBe('');
});
