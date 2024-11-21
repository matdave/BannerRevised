<?php

namespace BannerRevised\v2\Elements\Snippet;

class Snippet
{
    /**
     * A reference to the modX object.
     * @var \modX $modx
     */
    public $modx = null;

    protected $bannerrevised;

    /** @var array */
    protected $sp = [];

    public function __construct($bannerrevised, array $scriptProperties)
    {
        $this->bannerrevised =& $bannerrevised;
        $this->modx =& $this->bannerrevised->modx;
        $this->sp = $scriptProperties;
    }

    public function run()
    {
        $positions = $this->getOption('positions');
        $showLog = $this->getOption('showLog', false);
        $limit = $this->getOption('limit', 0);
        $offset = $this->getOption('offset', 0);
        $sortby = $this->getOption('sortby', 'RAND()');
        $sortdir = $this->getOption('sortdir', 'ASC');
        $outputSeparator = $this->getOption('outputSeparator', "\n");
        $extraWhere = $this->getOption('where');
        $showInactive = $this->getOption('showInactive', false);
        $tpl = $this->getOption('tpl', 'brevAd');
        $tplFirst = $this->getOption('tplFirst', $tpl);
        $tplLast = $this->getOption('tplLast', $tpl);
        $tplOdd = $this->getOption('tplOdd', $tpl);
        $tplImage = $this->getOption('tplImage', $tpl);
        $tplImageFirst = $this->getOption('tplImageFirst', $tplImage);
        $tplImageLast = $this->getOption('tplImageLast', $tplImage);
        $tplImageOdd = $this->getOption('tplImageOdd', $tplImage);
        $tplHTML = $this->getOption('tplHTML', $tpl);
        $tplHTMLFirst = $this->getOption('tplHTMLFirst', $tplHTML);
        $tplHTMLLast = $this->getOption('tplHTMLLast', $tplHTML);
        $tplHTMLOdd = $this->getOption('tplHTMLOdd', $tplHTML);
        $tplWrapper = $this->getOption('tplWrapper');
        $wrapIfEmpty = $this->getOption('wrapIfEmpty', false);
        $toPlaceholder = $this->getOption('toPlaceholder');
        $toSeparatePlaceholders = $this->getOption('toSeparatePlaceholders');

        $date = date('Y-m-d H:i:s');
        $where = [
            [
                'start' => null,
                'OR:start:<=' => $date
            ],[
                'end' => null,
                'OR:end:>=' => $date
            ]
        ];
        if ($showInactive) {
            $where['active'] = 1;
        }
        if ($positions) {
            $where['Positions.position:IN'] = explode(',', $positions);
        }

        if (!empty($extraWhere)) {
            $where = array_merge($where, json_encode($extraWhere));
        }

        if ($sortby == 'idx' || $sortby == 'index') {
            $sortby = 'Positions.idx';
        }

        $c = $this->modx->newQuery('brevAd');
        $c->select('brevAd.*, `Positions`.`idx`, `Positions`.`id` as `adposition`, `Positions`.`position` as `position`');
        $c->where($where);
        $c->sortby($sortby, $sortdir);
        $c->limit($limit, $offset);
        $c->leftJoin('brevAdPosition', 'Positions', '`brevAd`.`id` = `Positions`.`ad`');

        $rows = $this->modx->getCollection('brevAd', $c);

        $output = array();
        $default_source = $this->bannerrevised->getOption(
            'media_source',
            $this->sp,
            $this->modx->getOption('default_media_source')
        );
        $sources = array();
        $idx = 0;
        foreach ($rows as $object) {
            $row = $object->toArray();
            $source = !empty($row['source'])
                ? $row['source']
                : $default_source;

            if (!isset($sources[$row['source']])) {
                /**
                 *
                 *
                 * @var modMediaSource $source
                 */
                if ($source = $this->modx->getObject('sources.modMediaSource', $source)) {
                    $source->initialize($this->modx->context->key);
                }
                $sources[$row['source']] = $source;
            } else {
                $source = $sources[$row['source']];
            }

            if (!empty($source) && $source instanceof \modMediaSource && !empty($row['image'])) {
                $row['image'] = $source->getObjectUrl($row['image']);
            }

            $row['idx'] = $idx++;
            if (!empty($scriptProperties)) {
                $row = array_merge($scriptProperties, $row);
            }

            if ($row['type'] == 'image' && $tplImage !== $tpl) {
                $tpl = $tplImage;
                if ($idx == 1) {
                    $tpl = $tplImageFirst;
                } elseif ($idx == count($rows)) {
                    $tpl = $tplImageLast;
                } elseif ($idx % 2 == 1) {
                    $tpl = $tplImageOdd;
                }
            } elseif ($row['type'] == 'html' && $tplHTML !== $tpl) {
                $tpl = $tplHTML;
                if ($idx == 1) {
                    $tpl = $tplHTMLFirst;
                } elseif ($idx == count($rows)) {
                    $tpl = $tplHTMLLast;
                } elseif ($idx % 2 == 1) {
                    $tpl = $tplHTMLOdd;
                }
            } else {
                if ($idx == 1) {
                    $tpl = $tplFirst;
                } elseif ($idx == count($rows)) {
                    $tpl = $tplLast;
                } elseif ($idx % 2 == 1) {
                    $tpl = $tplOdd;
                }
            }


            $output[] = !empty($tpl)
                ? $this->modx->getChunk($tpl, $row)
                : '<pre>' . $this->modx->getChunk('', $row) . '</pre>';
        }

        if ($this->modx->user->hasSessionContext('mgr') && !empty($showLog)) {
            $output['log'] = '<pre class="pdoUsersLog">' . print_r(time(), 1) . '</pre>';
        }

// Return output
        if (!empty($toSeparatePlaceholders)) {
            $this->modx->setPlaceholders($output, $toSeparatePlaceholders);
        } else {
            $output = implode($outputSeparator, $output);

            if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
                $output = $this->modx->getChunk($tplWrapper, array('output' => $output));
            }

            if (!empty($toPlaceholder)) {
                $this->modx->setPlaceholder($toPlaceholder, $output);
            } else {
                return $output;
            }
        }
        return '';
    }

    protected function getOption($key, $default = null, $skipEmpty = true)
    {
        return $this->modx->getOption($key, $this->sp, $default, $skipEmpty);
    }
}
