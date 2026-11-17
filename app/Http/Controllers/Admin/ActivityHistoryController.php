<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\CareerEvent;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PBI-09 Riwayat aktivitas admin (PRD §7 F3).
 *
 * Admin hanya melihat materi, kuis, dan event yang dibuatnya sendiri;
 * super admin melihat milik semua admin. Data selalu dibatasi lewat query
 * berdasarkan user yang login (tidak ada ID di URL).
 */
class ActivityHistoryController extends Controller
{
    /** @var list<string> */
    public const TABS = ['materials', 'quizzes', 'events'];

    private const PER_PAGE = 10;

    public function index(Request $request): View
    {
        $user = $request->user()->load('profile');
        $isSuperAdmin = $user->isSuperAdmin();

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'materials';
        $search = trim((string) $request->query('q', ''));

        // Badge jumlah per tab (tanpa filter pencarian) — satu COUNT ringan per tab.
        $counts = collect(self::TABS)
            ->mapWithKeys(fn (string $name) => [$name => $this->baseQuery($name, $user)->count()]);

        $items = $this->listQuery($tab, $user, $isSuperAdmin)
            ->when($search !== '', fn (Builder $query) => $query->where('title', 'like', '%'.$this->escapeLike($search).'%'))
            ->latest('updated_at')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.history.index', compact('user', 'isSuperAdmin', 'tab', 'search', 'counts', 'items'));
    }

    /**
     * Query dasar per tab, sudah dibatasi ke pembuat bila bukan super admin.
     */
    private function baseQuery(string $tab, User $user): Builder
    {
        [$model, $ownerColumn] = match ($tab) {
            'quizzes' => [Quiz::class, 'created_by'],
            'events' => [CareerEvent::class, 'admin_id'],
            default => [LearningMaterial::class, 'admin_id'],
        };

        return $model::query()
            ->when(! $user->isSuperAdmin(), fn (Builder $query) => $query->where($ownerColumn, $user->id));
    }

    /**
     * Query tabel tab aktif dengan eager loading & withCount agar bebas N+1.
     */
    private function listQuery(string $tab, User $user, bool $isSuperAdmin): Builder
    {
        $query = $this->baseQuery($tab, $user);

        return match ($tab) {
            'quizzes' => $query
                ->with('material:id,title')
                ->when($isSuperAdmin, fn (Builder $q) => $q->with('creator:id,name'))
                ->withCount(['questions', 'attempts']),

            'events' => $query
                ->when($isSuperAdmin, fn (Builder $q) => $q->with('admin:id,name'))
                ->withCount(['registrations as registrants_count' => fn (Builder $q) => $q
                    ->where('status', '!=', RegistrationStatus::Cancelled->value)]),

            default => $query
                ->when($isSuperAdmin, fn (Builder $q) => $q->with('admin:id,name')),
        };
    }

    /**
     * Agar "%" dan "_" yang diketik admin dicari sebagai teks biasa.
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
