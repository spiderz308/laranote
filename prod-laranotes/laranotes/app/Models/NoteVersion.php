<?php

namespace App\Models;

use Database\Factories\NoteVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['note_id', 'user_id', 'title', 'content'])]
class NoteVersion extends Model
{
    /** @use HasFactory<NoteVersionFactory> */
    use HasFactory;

    /**
     * The note this version belongs to.
     *
     * @return BelongsTo<Note, $this>
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * The user that created this version.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
