<?php

namespace BannerRevised\v2\Processors\Ads;

class GetList extends \modObjectGetListProcessor
{
    public $classKey = 'brevAd';
    public $languageTopics = array('bannerrevised:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    public $objectType = 'bannerrevised.ad';

    public function prepareQueryBeforeCount(\xPDOQuery $c)
    {
        // Filter by position
        if ($position = $this->getProperty('position')) {
            $mode = $this->getProperty('mode', 'include');

            $q = $this->modx->newQuery('brevAdPosition');
            $q->select('ad');
            $q->where(array('position' => $position));
            if ($q->prepare() && $q->stmt->execute()) {
                $ads = array_unique($q->stmt->fetchAll(PDO::FETCH_COLUMN));
            }
            if (!empty($ads)) {
                if ($mode == 'exclude') {
                    $c->where(array('id:NOT IN' => $ads));
                } else {
                    $c->where(array('id:IN' => $ads));
                }
            }
        }
        // Filter by search query
        if ($query = $this->getProperty('query')) {
            $c->where(array('name:LIKE' => "%$query%", 'OR:description:LIKE' => "%$query%"));
        }

        return $c;
    }

    public function prepareRow(\xPDOObject $object)
    {
        /**
         * @var brevAd $object
         */
        $row = $object->toArray();
        $row['clicks'] = $this->modx->getCount('brevClick', array('ad' => $row['id']));
        $row['current_image'] = $object->getImageUrl();

        if (preg_match('/\[\[\~([0-9]{1,})\]\]$/', $row['url'], $matches)) {
            if ($resource = $this->modx->getObject('modResource', $matches[1])) {
                $row['url'] = '<sup>(' . $resource->id . ')</sup> ' . $resource->pagetitle;
            }
        }

        return $row;
    }
}
