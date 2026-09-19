<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        // Sanctum treats a request as the SPA (cookie session, CSRF) only when
        // it carries an Origin it trusts. Every feature test plays the SPA.
        $this->withHeader('Origin', 'http://localhost');
    })
    ->in('Feature');
