<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use RuntimeException;

/**
 * A unit's spoken gate phrase for one day, for visitors without a smartphone.
 * Uniqueness per day is enforced by the (phrase_date, phrase) index; on a
 * collision we simply draw another phrase.
 */
class UnitAccessPhrase extends Model
{
    private const MAX_ALLOCATION_TRIES = 10;

    // Short, distinct, easy-to-say words. Two words + a two-digit number gives
    // ~1.4M combinations per day.
    private const WORDS = [
        'AMBER', 'ANCHOR', 'APPLE', 'ARROW', 'BAMBOO', 'BANANA', 'BASKET', 'BEACON',
        'BISCUIT', 'BLOSSOM', 'BRIDGE', 'BUCKET', 'BUTTER', 'CABIN', 'CAMEL', 'CANDLE',
        'CANOE', 'CARROT', 'CASTLE', 'CEDAR', 'CHERRY', 'CIRCLE', 'CLOUD', 'COCOA',
        'COCONUT', 'COMET', 'COPPER', 'CORAL', 'COTTON', 'CRYSTAL', 'DAISY', 'DESERT',
        'DIAMOND', 'DOLPHIN', 'DRAGON', 'DRUM', 'EAGLE', 'EMBER', 'FALCON', 'FEATHER',
        'FERRY', 'FIDDLE', 'FOREST', 'FOUNTAIN', 'GARDEN', 'GINGER', 'GLACIER', 'GOLDEN',
        'GRAPE', 'GUITAR', 'HAMMER', 'HARBOR', 'HONEY', 'HORIZON', 'ISLAND', 'IVORY',
        'JACKET', 'JASMINE', 'JUNGLE', 'KETTLE', 'KITE', 'LADDER', 'LANTERN', 'LEMON',
        'LILY', 'LION', 'LOTUS', 'MANGO', 'MAPLE', 'MARBLE', 'MEADOW', 'MELON',
        'MIRROR', 'MONKEY', 'MOUNTAIN', 'NUTMEG', 'OCEAN', 'OLIVE', 'ORANGE', 'ORCHID',
        'OTTER', 'PADDLE', 'PALM', 'PANDA', 'PARROT', 'PEACH', 'PEARL', 'PEPPER',
        'PILLOW', 'PLANET', 'POCKET', 'PUMPKIN', 'RABBIT', 'RAINBOW', 'RIVER', 'ROCKET',
        'SADDLE', 'SAFARI', 'SAILOR', 'SILVER', 'SPARROW', 'SPIDER', 'SPRING', 'SUNSET',
        'TABLE', 'TEAPOT', 'THUNDER', 'TIGER', 'TOMATO', 'TRUMPET', 'TULIP', 'TURTLE',
        'VALLEY', 'VELVET', 'VIOLIN', 'WAGON', 'WALNUT', 'WHISTLE', 'WILLOW', 'WINDOW',
        'WINTER', 'ZEBRA',
    ];

    protected $fillable = [
        'unit_id',
        'estate_id',
        'phrase_date',
        'phrase',
        'failed_attempts',
    ];

    protected $casts = [
        'failed_attempts' => 'integer',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public static function today(): string
    {
        return now(config('access.phrase_timezone'))->toDateString();
    }

    public static function findForToday(Unit $unit): ?self
    {
        return static::where('unit_id', $unit->id)
            ->where('phrase_date', static::today())
            ->first();
    }

    /**
     * Today's phrase for the unit, allocating one on first request.
     */
    public static function forUnit(Unit $unit): self
    {
        for ($try = 0; $try < self::MAX_ALLOCATION_TRIES; $try++) {
            if ($existing = static::findForToday($unit)) {
                return $existing;
            }

            try {
                return static::create([
                    'unit_id'     => $unit->id,
                    'estate_id'   => $unit->estate_id,
                    'phrase_date' => static::today(),
                    'phrase'      => static::generatePhrase(),
                ]);
            } catch (UniqueConstraintViolationException) {
                // Another request created this unit's row first, or the phrase
                // is taken by another unit today. Loop re-checks and redraws.
            }
        }

        throw new RuntimeException('Could not allocate a unique access phrase.');
    }

    /**
     * Replace today's phrase (e.g. it leaked) and clear the lockout.
     */
    public function rotate(): self
    {
        for ($try = 0; $try < self::MAX_ALLOCATION_TRIES; $try++) {
            try {
                $this->update([
                    'phrase'          => static::generatePhrase(),
                    'failed_attempts' => 0,
                ]);

                return $this;
            } catch (UniqueConstraintViolationException) {
                // Phrase taken by another unit today — draw again.
            }
        }

        throw new RuntimeException('Could not allocate a unique access phrase.');
    }

    public function matches(string $input): bool
    {
        return hash_equals($this->phrase, static::normalise($input));
    }

    public function isLocked(): bool
    {
        return $this->failed_attempts >= config('access.max_failed_attempts');
    }

    public function attemptsRemaining(): int
    {
        return max(0, config('access.max_failed_attempts') - $this->failed_attempts);
    }

    /**
     * 'amber-lion 27', ' Amber  Lion27 ' etc. all become 'AMBER LION 27'.
     */
    public static function normalise(string $input): string
    {
        $upper = strtoupper($input);
        $spaced = preg_replace('/([A-Z])(\d)/', '$1 $2', $upper);

        return trim(preg_replace('/[^A-Z0-9]+/', ' ', $spaced));
    }

    private static function generatePhrase(): string
    {
        $count = count(self::WORDS);
        $first = random_int(0, $count - 1);
        do {
            $second = random_int(0, $count - 1);
        } while ($second === $first);

        return self::WORDS[$first] . ' ' . self::WORDS[$second] . ' ' . random_int(10, 99);
    }
}
