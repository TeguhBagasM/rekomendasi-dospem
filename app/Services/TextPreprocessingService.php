<?php

namespace App\Services;

class TextPreprocessingService
{
    /**
     * Daftar stopword Bahasa Indonesia + Inggris umum.
     */
    private const STOPWORDS = [
        'dan', 'untuk', 'pada', 'dengan', 'yang', 'menggunakan', 'dalam', 'adalah', 'di', 'ke', 'dari',
        'ini', 'itu', 'atau', 'juga', 'sudah', 'akan', 'bisa', 'dapat', 'sebagai', 'karena', 'oleh',
        'tentang', 'antara', 'the', 'a', 'an', 'of', 'to', 'in', 'on', 'for', 'with', 'is', 'are',
    ];

    /**
     * Case folding + hapus karakter non alfanumerik.
     */
    public function normalize(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/u', '', $text) ?? '';

        return trim($text);
    }

    /**
     * Tokenizing + stopword removal.
     */
    public function tokenize(string $text): array
    {
        $normalized = $this->normalize($text);

        if ($normalized === '') {
            return [];
        }

        $tokens = preg_split('/\s+/', $normalized) ?: [];

        return array_values(array_filter(
            $tokens,
            fn (string $token): bool => $token !== '' && ! in_array($token, self::STOPWORDS, true)
        ));
    }
}
