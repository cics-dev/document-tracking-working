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

it('preserves editable table rows and formatting while removing unsafe cell attributes', function () {
    $html = '<table><tbody><tr><td data-row="row-a" onclick="alert(1)"><strong>Item</strong></td><td data-row="row-a" class="ql-align-right">Amount</td></tr><tr><td data-row="row-b">Supplies</td><td data-row="row-b"><script>alert(1)</script>100</td></tr></tbody></table>';
    $clean = DocumentHtml::sanitize($html);

    foreach (['<table>', '<tbody>', '<tr>', 'data-row="row-a"', 'data-row="row-b"', '<strong>Item</strong>', 'ql-align-right', 'Supplies', '100'] as $format) {
        expect($clean)->toContain($format);
    }
    expect($clean)->not->toContain('onclick', '<script');
    expect(DocumentHtml::sanitize($clean))->toBe($clean);
    $document = new Document;
    $document->content = $html;
    expect($document->content)->toBe($clean);
});

it('preserves resized table column widths without allowing executable width attributes', function () {
    $html = '<table><tbody><tr><td data-row="row-a" width="65.5000%">Wide</td><td data-row="row-a" width="34.5000%">Narrow</td></tr></tbody></table>';
    $clean = DocumentHtml::sanitize($html);

    expect($clean)->toContain('width="65.5000%"', 'width="34.5000%"');
    expect(DocumentHtml::sanitize($clean))->toBe($clean);
    expect(DocumentHtml::sanitize('<table><tr><td width="javascript:alert(1)">Safe</td></tr></table>'))
        ->not->toContain('javascript:', 'width=');
});

it('preserves merged cells and outer table sizing while stripping unsafe table styles', function () {
    $html = '<table style="width: 80%; margin-left: 5%; background-image: url(https://example.test/x);"><tbody><tr><td data-row="a" colspan="2" rowspan="2" width="100%" data-original-width="50%" data-table-width="80%" data-table-offset="5%">Merged</td><td data-row="a" data-cell-covered="true" width="50%"></td></tr><tr><td data-row="b" data-cell-covered="true" width="50%"></td><td data-row="b" data-cell-covered="true" width="50%"></td></tr></tbody></table>';
    $clean = DocumentHtml::sanitize($html);

    expect($clean)->toContain('colspan="2"', 'rowspan="2"', 'data-cell-covered="true"', 'data-original-width="50%"', 'data-table-width="80%"', 'data-table-offset="5%"', 'width: 80%', 'margin-left: 5%');
    expect($clean)->not->toContain('background-image', 'https://');
    expect(DocumentHtml::sanitize($clean))->toBe($clean);
});

it('preserves in-cell line breaks and resized row heights', function () {
    $html = '<table><tr><td data-row="a" data-table-row-heights="80,40" height="80">First<span class="document-cell-break"><span contenteditable="false"><br></span></span>Second</td></tr><tr><td data-row="b" data-table-row-heights="80,40" height="40">Last</td></tr></table>';
    $clean = DocumentHtml::sanitize($html);

    expect($clean)->toContain('document-cell-break', '<br', 'First', 'Second', 'height="80"', 'height="40"', 'data-table-row-heights="80,40"');
    expect($clean)->not->toContain('contenteditable');
    expect(DocumentHtml::sanitize($clean))->toBe($clean);
    expect(DocumentHtml::sanitize('<table><tr><td data-table-row-heights="24,bad" height="javascript:alert(1)">Safe</td></tr></table>'))
        ->not->toContain('data-table-row-heights', 'javascript:');
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
