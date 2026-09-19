<?php

it('boots and answers the health check', function () {
    $this->get('/up')->assertOk();
});
