<?php
namespace App\Repositories\Language;

use App\Repositories\Language\LanguageInterface;
use Illuminate\Http\Request;
use App\Models\Language;
use Illuminate\Pagination\LengthAwarePaginator;

class LanguageRepository implements LanguageInterface
{
    /**
     * @var App\Models\Language
     */
    private $language;

    /**
     * Create a new Language repository instance.
     *
     * @param App\Models\Language
     * @return void
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Get listing of language
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getLanguageList(Request $request): LengthAwarePaginator
    {
        $languageQuery = $this->language;

        // Order by filters
        if ($request->has('order')) {
            $languageQuery = $languageQuery->orderBy('created_at', $request->order);
        }
        // Search filter
        if ($request->has('search')) {
            $languageQuery = $languageQuery->where('name', 'like', '%' . $request->search . '%');
        }

        return $languageQuery->paginate($request->perPage);
    }

    /**
     * Display language detail.
     *
     * @param  int  $id
     * @return App\Models\Language
     */
    public function find(int $id): Language
    {
        return $this->language->findOrFail($id);
    }

    /**
     * Store language data
     *
     * @param  array $languageData
     * @return App\Models\Language
     */
    public function store(array $languageData): Language
    {
        return $this->language->create($languageData);
    }

    /**
     * Update language details in storage.
     *
     * @param  array $languageData
     * @param  int  $id
     * @return App\Models\Language
     */
    public function update(array $languageData, int $id): Language
    {
        $languageDetails = $this->language->findOrFail($id);
        $languageDetails->update($languageData);
        return $languageDetails;
    }

    /**
     * Delete language by id.
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $languageData = $this->language->findOrFail($id);
        return $languageData->delete();
    }
}
