<?php

class sspmod_userregistration_Paginate {

    private $elements;

    private $page;

    private $per_page;

    private $base_url;

    public function __construct($elements, $per_page) {
        $this->elements = $elements;
        if ($per_page <= 0) {
            $this->per_page = 1;
        } else {
            $this->per_page = $per_page;
        }
    }

    public function setPage($page) {
        if ($page <= 0) {
            $this->page = 1;
        } else {
            $this->page = $page;
        }
    }

    public function setBaseURL($url) {
        $this->base_url = $url;
    }

    public function getPageElements($page = null) {
        if ($page !== null) {
            $this->setPage($page);
        }

        $offset = ($this->page-1)*$this->per_page;

        if ($offset >= count($this->elements)) {
            $this->setPage(1);
            $offset = 0;
        }

        return array_slice($this->elements, $offset, $this->per_page);
    }

    public function getButtons() {
        $total_elems = count($this->elements);
        $pages = ceil($total_elems/$this->per_page);
        $text = '<div class="pages"><div class="btn-toolbar"><div class="btn-group">';
        // Previous page
        for($i=1;$i<=$pages;$i++) {
            $classes = array();
            if ($i == $this->page) {
                $classes = array('btn-primary');
            }
            $text .= $this->getButton($i, $classes);
        }

        $text .= '</div></div></div>';

        return $text;
    }

    private function getButton($page, $classes = array()) {
        $classes[] = 'btn';
        $text = '<a class="' . implode(' ', $classes) . '"'
            .' href="'
            . SimpleSAML_Utilities::addURLparameter(
                $this->base_url,
                array(
                    'page' => $page
                )
            ) . '">' . $page . '</a>';

        return $text;
    }

}
