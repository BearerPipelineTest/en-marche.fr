<?php

namespace App\Content;

use App\Entity\Page;

class PageFactory
{
    public function createFromArray(array $data): Page
    {
        $page = new Page();
        $page->setTitle($data['title']);
        $page->setSlug($data['slug']);
        $page->setDescription($data['description']);
        $page->setContent($data['content']);
        $page->setMedia($data['media'] ?? null);
        $page->setHeaderMedia($data['headerMedia'] ?? null);
        $page->setDisplayMedia(false);
        $page->setKeywords($data['keywords'] ?? '');
        $page->setLayout($data['layout'] ?? Page::LAYOUT_DEFAULT);

        return $page;
    }
}
