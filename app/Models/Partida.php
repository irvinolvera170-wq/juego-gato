<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['jugador_x_id', 'jugador_o_id', 'turno', 'tablero', 'estado', 'ganador_id'])]
class Partida extends Model
{
    public const ACTIVAS = ['esperando', 'en_curso'];

    // Las 8 líneas ganadoras: 3 filas, 3 columnas y 2 diagonales
    public const LINEAS = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8],
        [0, 4, 8], [2, 4, 6],
    ];

    // withTrashed: el historial conserva el nombre de usuarios eliminados lógicamente
    public function jugadorX(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jugador_x_id')->withTrashed();
    }

    public function jugadorO(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jugador_o_id')->withTrashed();
    }

    public function ganador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ganador_id')->withTrashed();
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ACTIVAS);
    }

    // Agrupa el OR entre paréntesis para no romper otros filtros
    public function scopeDeUsuario(Builder $query, int $userId): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('jugador_x_id', $userId)
            ->orWhere('jugador_o_id', $userId));
    }

    public function estaActiva(): bool
    {
        return in_array($this->estado, self::ACTIVAS, true);
    }

    // 'X', 'O' o null si el usuario no participa en esta partida
    public function simboloDe(User $user): ?string
    {
        return match (true) {
            (int) $this->jugador_x_id === $user->id => 'X',
            (int) $this->jugador_o_id === $user->id => 'O',
            default => null,
        };
    }

    public function oponenteDe(User $user): ?User
    {
        return $this->simboloDe($user) === 'X' ? $this->jugadorO : $this->jugadorX;
    }

    // Devuelve la línea de tres casillas iguales, o null si no hay ganador
    public static function lineaGanadora(string $tablero): ?array
    {
        foreach (self::LINEAS as [$a, $b, $c]) {
            if ($tablero[$a] !== '-' && $tablero[$a] === $tablero[$b] && $tablero[$a] === $tablero[$c]) {
                return [$a, $b, $c];
            }
        }

        return null;
    }

    public static function ganadorEn(string $tablero): ?string
    {
        $linea = self::lineaGanadora($tablero);

        return $linea ? $tablero[$linea[0]] : null;
    }

    // [color de Bootstrap, texto] según el estado y quién lo mira
    public function resultadoPara(?User $user): array
    {
        $participa = $user && $this->simboloDe($user) !== null;
        $gano = $user && (int) $this->ganador_id === $user->id;

        if ($this->estado === 'esperando') {
            return ['warning', 'Esperando oponente'];
        }
        if ($this->estado === 'en_curso') {
            return ['primary', 'En curso'];
        }
        if ($this->estado === 'empate') {
            return ['secondary', 'Empate'];
        }
        if ($this->estado === 'cancelada') {
            return ['dark', 'Cancelada por el administrador'];
        }
        if ($this->estado === 'ganada') {
            if (!$participa) {
                return ['success', 'Ganó '.$this->ganador?->name];
            }

            return $gano ? ['success', 'Ganaste'] : ['danger', 'Perdiste'];
        }

        // abandonada
        if (!$this->ganador_id) {
            return ['secondary', 'Abandonada sin oponente'];
        }
        if (!$participa) {
            return ['secondary', 'Abandonada; ganó '.$this->ganador?->name];
        }

        return $gano ? ['success', 'Ganaste (tu oponente abandonó)'] : ['danger', 'Abandonaste la partida'];
    }

    public function mensajePara(?User $user): string
    {
        $simbolo = $user ? $this->simboloDe($user) : null;

        if ($this->estado === 'esperando') {
            return 'Esperando a que otro jugador se una...';
        }

        if ($this->estado === 'en_curso') {
            if ($simbolo === null) {
                return "Turno de {$this->turno}";
            }

            return $this->turno === $simbolo ? 'Es tu turno' : 'Turno de tu oponente...';
        }

        return $this->resultadoPara($user)[1];
    }

    // Estado que consume el navegador en cada consulta (polling)
    public function paraJson(?User $user): array
    {
        $this->loadMissing(['jugadorX', 'jugadorO', 'ganador']);

        $simbolo = $user ? $this->simboloDe($user) : null;
        $miTurno = $this->estado === 'en_curso' && $simbolo !== null && $simbolo === $this->turno;

        $color = $this->resultadoPara($user)[0];
        if ($this->estado === 'en_curso') {
            $color = $miTurno ? 'success' : 'info';
        }

        return [
            'id' => $this->id,
            'tablero' => str_split($this->tablero),
            'turno' => $this->turno,
            'estado' => $this->estado,
            'terminada' => !$this->estaActiva(),
            'mi_simbolo' => $simbolo,
            'es_mi_turno' => $miTurno,
            'mensaje' => $this->mensajePara($user),
            'color' => $color,
            'jugadores' => [
                'X' => $this->jugadorX->name,
                'O' => $this->jugadorO?->name,
            ],
            'linea_ganadora' => $this->estado === 'ganada' ? self::lineaGanadora($this->tablero) : null,
        ];
    }
}
