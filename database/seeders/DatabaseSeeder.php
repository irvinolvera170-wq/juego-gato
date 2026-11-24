<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@test.com',
            'password' => 'password',   // el cast "hashed" del modelo la cifra
            'role' => 'admin',
        ]);

        $jugadores = [1 => 'Ana López', 2 => 'Luis Pérez', 3 => 'María Gómez', 4 => 'Carlos Ruiz'];

        foreach ($jugadores as $n => $nombre) {
            User::create([
                'name' => $nombre,
                'email' => "jugador{$n}@test.com",
                'password' => 'password',
                'role' => 'jugador',
            ]);
        }
    }
}