<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeduhKopi extends Command
{
    // Ini adalah perintah yang akan diketik di terminal
    protected $signature = 'app:seduh-kopi';

    // Deskripsi fitur aneh ini
    protected $description = 'Fitur rahasia untuk menyeduh kopi virtual saat developer pusing ngoding';

    public function handle()
    {
        $this->warn('Sistem POS mendeteksi developer sedang ngantuk...');
        sleep(1); // Jeda 1 detik biar dramatis
        
        $this->info('Memanaskan air... ♨️');
        $this->output->progressStart(100);
        for ($i = 0; $i < 100; $i++) {
            usleep(20000); // Animasi loading palsu
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->info('Menggiling biji kopi dari database... ⚙️');
        sleep(2);
        
        $this->info('Mengekstrak rasa dari tabel orders_items... ☕');
        sleep(2);

        // Menampilkan ASCII Art Kopi
        $this->line('<fg=yellow>
               (  )   (   )  )
                ) (   )  (  (
                ( )  (    ) )
                _____________
               <_____________> ___
               |             |/ _ \
               |               | | |
               |               |_| |
            ___|             |\___/
           /    \___________/    \
           \_____________________/
        </>');

        $this->info('Kopi virtual siap! Tetap semangat ngodingnya bosku! 🚀');
    }
}