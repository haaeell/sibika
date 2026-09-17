<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class ArticleContentSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'strong', 'em', 'b', 'i', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'a', 'img'];

    public function clean(string $html): string
    {
        $source = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $source->loadHTML('<?xml encoding="UTF-8"><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $target = new DOMDocument('1.0', 'UTF-8');
        $root = $target->createElement('div');
        $target->appendChild($root);

        foreach ($source->documentElement?->childNodes ?? [] as $child) {
            $this->appendSanitizedNode($child, $root, $target);
        }

        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $target->saveHTML($child);
        }

        return trim($html);
    }

    private function appendSanitizedNode(DOMNode $node, DOMNode $parent, DOMDocument $target): void
    {
        if ($node instanceof DOMText) {
            $parent->appendChild($target->createTextNode($node->nodeValue ?? ''));

            return;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style'], true)) {
            return;
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            foreach ($node->childNodes as $child) {
                $this->appendSanitizedNode($child, $parent, $target);
            }

            return;
        }

        $element = $target->createElement(match ($tag) {
            'b' => 'strong',
            'i' => 'em',
            default => $tag,
        });

        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));
            if ($this->safeUrl($href)) {
                $element->setAttribute('href', $href);
                $element->setAttribute('target', '_blank');
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }

        if ($tag === 'img') {
            $src = trim($node->getAttribute('src'));
            if (! $this->safeUrl($src)) {
                return;
            }

            $element->setAttribute('src', $src);
            $element->setAttribute('alt', mb_substr($node->getAttribute('alt'), 0, 120));
        }

        foreach ($node->childNodes as $child) {
            $this->appendSanitizedNode($child, $element, $target);
        }

        $parent->appendChild($element);
    }

    private function safeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        return str_starts_with($url, '/') || preg_match('/^https?:\/\//i', $url) === 1 || preg_match('/^mailto:/i', $url) === 1;
    }
}
