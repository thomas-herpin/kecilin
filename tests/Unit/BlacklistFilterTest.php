<?php

use App\Services\BlacklistFilter;

beforeEach(function () {
    $this->filter = new BlacklistFilter();
});

// --- isDomainBlocked() ---

test('isDomainBlocked returns true for a domain in the blacklist', function () {
    expect($this->filter->isDomainBlocked('http://malware.com/path'))->toBeTrue();
});

test('isDomainBlocked returns true for another blacklisted domain', function () {
    expect($this->filter->isDomainBlocked('https://phishing.net'))->toBeTrue();
});

test('isDomainBlocked returns false for a domain not in the blacklist', function () {
    expect($this->filter->isDomainBlocked('https://example.com'))->toBeFalse();
});

test('isDomainBlocked is case-insensitive for uppercase domain', function () {
    expect($this->filter->isDomainBlocked('http://MALWARE.COM/page'))->toBeTrue();
});

test('isDomainBlocked is case-insensitive for mixed-case domain', function () {
    expect($this->filter->isDomainBlocked('https://Phishing.Net'))->toBeTrue();
});

test('isDomainBlocked returns false for a safe domain with similar name', function () {
    // "notmalware.com" is not in the blacklist
    expect($this->filter->isDomainBlocked('https://notmalware.com'))->toBeFalse();
});

test('isDomainBlocked returns false for an empty string', function () {
    expect($this->filter->isDomainBlocked(''))->toBeFalse();
});

test('isDomainBlocked returns false for a URL with no host', function () {
    expect($this->filter->isDomainBlocked('not-a-url'))->toBeFalse();
});

test('isDomainBlocked handles URL with path and query string', function () {
    expect($this->filter->isDomainBlocked('http://spam.com/some/path?q=1'))->toBeTrue();
});

// --- getBlockedDomains() ---

test('getBlockedDomains returns an array', function () {
    expect($this->filter->getBlockedDomains())->toBeArray();
});

test('getBlockedDomains contains known blacklisted domains', function () {
    $domains = $this->filter->getBlockedDomains();
    expect($domains)->toContain('malware.com');
    expect($domains)->toContain('phishing.net');
});

test('getBlockedDomains returns non-empty list', function () {
    expect(count($this->filter->getBlockedDomains()))->toBeGreaterThan(0);
});
