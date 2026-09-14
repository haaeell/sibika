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
            $clean = $this->sanitizeNode($child, $target);
            if ($clean) {
                $root->appendChild($clean);
            }
        }

        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $target->saveHTML($child);
        }

        return trim($html);
    }

    private function sanitizeNode(DOMNode $node, DOMDocument $target): ?DOMNode
    {
        if ($node instanceof DOMText) {
            return $target->createTextNode($node->nodeValue ?? '');
        }

        if (! $node instanceof DOMElement) {
            return null;
        }

        $tag = strtolower($node->tagName);
        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            $fragment = $target->createDocumentFragment();
            foreach ($node->childNodes as $child) {
                $clean = $this->sanitizeNode($child, $target);
                if ($clean) {
                    $fragment->appendChild($clean);
                }
            }

            return $fragment;
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
                return null;
            }

            $element->setAttribute('src', $src);
            $element->setAttribute('alt', mb_substr($node->getAttribute('alt'), 0, 120));
        }

        foreach ($node->childNodes as $child) {
            $clean = $this->sanitizeNode($child, $target);
            if ($clean) {
                $element->appendChild($clean);
            }
        }

        return $element;
    }

    private function safeUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        return str_starts_with($url, '/') || preg_match('/^https?:\/\//i', $url) === 1 || preg_match('/^mailto:/i', $url) === 1;
    }
}
