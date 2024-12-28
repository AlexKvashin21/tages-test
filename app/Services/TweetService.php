<?php

namespace App\Services;

use App\Contracts\Repositories\TweetRepositoryContract;
use App\Contracts\Services\TweetServiceContract;
use App\DTO\Tweet\CreateTweetDTO;
use App\Events\StoreTweetEvent;
use App\Filters\TweetFilter;
use App\Models\Tweet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

readonly class TweetService implements TweetServiceContract
{
    public function __construct(
        private TweetRepositoryContract $repository,
    )
    {
    }

    /**
     * @param TweetFilter $filter
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginatedList(TweetFilter $filter, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginatedList($filter, $page, $perPage);
    }

    /**
     * @param TweetFilter $filter
     * @return Collection
     */
    public function list(TweetFilter $filter): Collection
    {
        return $this->repository->list($filter);
    }

    /**
     * @param int $id
     * @return Tweet
     */
    public function get(int $id): Tweet
    {
        return $this->repository->findOrFailById($id);
    }

    /**
     * @param CreateTweetDTO $createTweetDTO
     * @return Tweet
     */
    public function create(CreateTweetDTO $createTweetDTO): Tweet
    {
        $tweet = $this->repository->create($createTweetDTO);

        broadcast(new StoreTweetEvent($tweet));

        return $tweet;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
