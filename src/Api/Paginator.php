<?php

namespace DeskPRO\Api;

use DeskPROClient\Api\Request\FindRequest;
use DeskPROClient\Api\Response\ResponseInterface;

/**
 * Allow to paginate over the Resource Collection and automatically load next page from the API
 * Works only with Resources that have Meta data in the response
 */
class Paginator implements \Iterator
{
    const ITEMS_PER_PAGE_DEFAULT_COUNT = 50;

    /**
     *
     * @var FindRequest 
     */
    protected $findRequest;

    /**
     *
     * @var ResponseInterface
     */
    protected $lastResponse;

    /**
     *
     * @param FindRequest $findRequest
     */
    public function __construct(FindRequest $findRequest)
    {
        $this->findRequest = $findRequest;
    }

    /**
     *
     * @return ResponseInterface
     */
    public function current()
    {
        return $this->lastResponse;
    }

    /**
     * @return ResponseInterface
     */
    public function next()
    {
        if ($this->isOnLastPage()) {
            $this->lastResponse = false;
        } else {
            $page = $this->findRequest->getPage();
            $count = $this->findRequest->getCount();
            $this->lastResponse = $this->findRequest
                ->page($page ? ++$page : 1)
                ->count($count ? $count : self::ITEMS_PER_PAGE_DEFAULT_COUNT)
                ->send();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->findRequest->getPage();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->lastResponse
                && $this->lastResponse->getMeta()
                && $this->lastResponse->getMeta()['pagination']
                && $this->lastResponse->getMeta()['pagination']['count'];
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->findRequest->page(null);
        $this->lastResponse = null;
        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->lastResponse
               ? $this->lastResponse->getMeta()['pagination']['total_pages']
               : false;
    }

    /**
     *
     * @return bool
     */
    protected function isOnLastPage()
    {
        return  $this->lastResponse
                ? $this->findRequest->getPage() == $this->count()
                : false;
    }
}