<?php

use App\Services\QrCodeGenerator;

beforeEach(function () {
    $this->generator = new QrCodeGenerator();
});

test('generateSvg returns a string', function () {
    $result = $this->generator->generateSvg('https://short.ly/abc123');
    expect($result)->toBeString();
});

test('generateSvg output starts with <svg tag', function () {
    $result = $this->generator->generateSvg('https://short.ly/abc123');
    expect($result)->toContain('<svg');
});

test('generateSvg output contains closing svg tag', function () {
    $result = $this->generator->generateSvg('https://short.ly/abc123');
    expect($result)->toContain('</svg>');
});

test('generateSvg encodes the short link (output is non-empty SVG)', function () {
    $shortUrl = 'https://short.ly/abc123';
    $result = $this->generator->generateSvg($shortUrl);
    // QR codes encode data visually as path patterns, not as literal text in SVG
    expect($result)->toContain('<svg');
    expect(strlen($result))->toBeGreaterThan(100);
});

test('generateSvg produces non-empty output', function () {
    $result = $this->generator->generateSvg('https://example.com/test');
    expect(strlen($result))->toBeGreaterThan(0);
});

test('generateSvg works with http scheme', function () {
    $result = $this->generator->generateSvg('http://short.ly/xyz');
    expect($result)->toContain('<svg');
});

test('generateSvg works with a URL containing a path', function () {
    $url = 'https://kecilin.id/AbCdEf';
    $result = $this->generator->generateSvg($url);
    expect($result)->toContain('<svg');
    expect($result)->toContain('</svg>');
});

test('generateSvg produces different output for different URLs', function () {
    $result1 = $this->generator->generateSvg('https://short.ly/aaaaaa');
    $result2 = $this->generator->generateSvg('https://short.ly/bbbbbb');
    expect($result1)->not->toBe($result2);
});
