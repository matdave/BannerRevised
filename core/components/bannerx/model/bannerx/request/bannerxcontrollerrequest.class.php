<?php
require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';
/**
 * Encapsulates the interaction of MODx manager with an HTTP request.
 *
 * {@inheritdoc}
 *
 * @package bannerx
 * @extends modRequest
 */
class bannerxControllerRequest extends modRequest {
    public $actionVar = 'action';
    public $defaultAction = 'index';

    function __construct(BannerX &$bannerx) {
        parent :: __construct($bannerx->modx);
        $this->bannerx =& $bannerx;
    }

    /**
     * Extends modRequest::handleRequest and loads the proper error handler and
     * actionVar value.
     *
     * {@inheritdoc}
     */
    public function handleRequest() {
        $this->loadErrorHandler();

        /* save page to manager object. allow custom actionVar choice for extending classes. */
        $this->action = isset($_REQUEST[$this->actionVar]) ? $_REQUEST[$this->actionVar] : $this->defaultAction;

        $modx =& $this->modx;
        $bannerx =& $this->bannerx;
        $viewHeader = include $this->bannerx->config['corePath'].'controllers/mgr/header.php';

        $f = $this->bannerx->config['corePath'].'controllers/mgr/'.$this->action.'.php';
        if (file_exists($f)) {
            $viewOutput = include $f;
        } else {
            $viewOutput = 'Controller not found: '.$f;
        }

        return $viewHeader.$viewOutput;
    }
}