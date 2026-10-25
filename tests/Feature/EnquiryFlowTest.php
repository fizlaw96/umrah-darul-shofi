<?php

it('returns 404 for the retired public contact page', function () {
    $this->get('/hubungi-kami')->assertNotFound();
    $this->post('/hubungi-kami')->assertNotFound();
});
