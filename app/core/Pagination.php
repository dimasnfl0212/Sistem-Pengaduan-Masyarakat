<?php
// File: C:\xampp\htdocs\lapor-system\app\core\Pagination.php

class Pagination {
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    private $maxPagesToShow = 5;
    private $urlPattern;
    
    public function __construct($totalItems, $itemsPerPage, $currentPage, $urlPattern = '?page={page}') {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = ceil($totalItems / $itemsPerPage);
        $this->urlPattern = $urlPattern;
    }
    
    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    public function hasNextPage() {
        return $this->currentPage < $this->totalPages;
    }
    
    public function hasPreviousPage() {
        return $this->currentPage > 1;
    }
    
    public function getNextPage() {
        if ($this->hasNextPage()) {
            return $this->currentPage + 1;
        }
        return null;
    }
    
    public function getPreviousPage() {
        if ($this->hasPreviousPage()) {
            return $this->currentPage - 1;
        }
        return null;
    }
    
    public function getPages() {
        $pages = [];
        
        if ($this->totalPages <= $this->maxPagesToShow) {
            // Show all pages
            for ($i = 1; $i <= $this->totalPages; $i++) {
                $pages[] = $i;
            }
        } else {
            // Show pages around current page
            $half = floor($this->maxPagesToShow / 2);
            
            if ($this->currentPage <= $half) {
                // Near beginning
                for ($i = 1; $i <= $this->maxPagesToShow; $i++) {
                    $pages[] = $i;
                }
            } elseif ($this->currentPage >= $this->totalPages - $half) {
                // Near end
                for ($i = $this->totalPages - $this->maxPagesToShow + 1; $i <= $this->totalPages; $i++) {
                    $pages[] = $i;
                }
            } else {
                // In the middle
                for ($i = $this->currentPage - $half; $i <= $this->currentPage + $half; $i++) {
                    $pages[] = $i;
                }
            }
        }
        
        return $pages;
    }
    
    public function getPageUrl($page) {
        return str_replace('{page}', $page, $this->urlPattern);
    }
    
    public function render($options = []) {
        $defaults = [
            'ulClass' => 'pagination',
            'liClass' => 'page-item',
            'linkClass' => 'page-link',
            'activeClass' => 'active',
            'disabledClass' => 'disabled',
            'prevText' => '&laquo;',
            'nextText' => '&raquo;',
            'ellipsisText' => '...'
        ];
        
        $options = array_merge($defaults, $options);
        
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $html = '<ul class="' . $options['ulClass'] . '">';
        
        // Previous button
        if ($this->hasPreviousPage()) {
            $html .= '<li class="' . $options['liClass'] . '">';
            $html .= '<a class="' . $options['linkClass'] . '" href="' . $this->getPageUrl($this->getPreviousPage()) . '">';
            $html .= $options['prevText'] . '</a></li>';
        } else {
            $html .= '<li class="' . $options['liClass'] . ' ' . $options['disabledClass'] . '">';
            $html .= '<span class="' . $options['linkClass'] . '">' . $options['prevText'] . '</span></li>';
        }
        
        // Page numbers
        $pages = $this->getPages();
        $lastPage = 0;
        
        foreach ($pages as $page) {
            if ($page > $lastPage + 1) {
                $html .= '<li class="' . $options['liClass'] . ' ' . $options['disabledClass'] . '">';
                $html .= '<span class="' . $options['linkClass'] . '">' . $options['ellipsisText'] . '</span></li>';
            }
            
            $html .= '<li class="' . $options['liClass'] . ($page == $this->currentPage ? ' ' . $options['activeClass'] : '') . '">';
            
            if ($page == $this->currentPage) {
                $html .= '<span class="' . $options['linkClass'] . '">' . $page . '</span>';
            } else {
                $html .= '<a class="' . $options['linkClass'] . '" href="' . $this->getPageUrl($page) . '">' . $page . '</a>';
            }
            
            $html .= '</li>';
            $lastPage = $page;
        }
        
        // Next button
        if ($this->hasNextPage()) {
            $html .= '<li class="' . $options['liClass'] . '">';
            $html .= '<a class="' . $options['linkClass'] . '" href="' . $this->getPageUrl($this->getNextPage()) . '">';
            $html .= $options['nextText'] . '</a></li>';
        } else {
            $html .= '<li class="' . $options['liClass'] . ' ' . $options['disabledClass'] . '">';
            $html .= '<span class="' . $options['linkClass'] . '">' . $options['nextText'] . '</span></li>';
        }
        
        $html .= '</ul>';
        
        // Show page info
        $html .= '<div class="pagination-info mt-2 text-muted">';
        $html .= 'Menampilkan ' . (($this->currentPage - 1) * $this->itemsPerPage + 1) . ' - ';
        $html .= min($this->currentPage * $this->itemsPerPage, $this->totalItems) . ' dari ' . $this->totalItems . ' laporan';
        $html .= '</div>';
        
        return $html;
    }
}
?>