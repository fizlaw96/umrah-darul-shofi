<?php

use Illuminate\Support\Facades\Artisan;

it('renders the maintenance page while the website is down', function () {
    Artisan::call('down', [
        '--render' => 'errors.503',
        '--retry' => 60,
    ]);

    try {
        $this->get('/')
            ->assertServiceUnavailable()
            ->assertSee('SalamHaji Travel & Tours')
            ->assertSee('Kami sedang menjalankan kerja penyelenggaraan');
    } finally {
        Artisan::call('up');
    }
});
