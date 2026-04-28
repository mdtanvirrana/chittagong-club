@php
    static $canterburyFontDataUri = null;

    if ($canterburyFontDataUri === null) {
        $canterburyFontPath = public_path('assets/canterbury/Canterbury.ttf');
        $canterburyFontDataUri = is_file($canterburyFontPath)
            ? 'data:font/ttf;base64,' . base64_encode(file_get_contents($canterburyFontPath))
            : null;
    }
@endphp

<style>
    @if ($canterburyFontDataUri)
        @font-face {
            font-family: 'Canterbury';
            src: url('{{ $canterburyFontDataUri }}') format('truetype');
            font-style: normal;
            font-weight: 400;
            font-display: swap;
        }
    @endif

    .company-name-canterbury {
        display: inline-block;
        font-family: 'Canterbury', serif !important;
        font-weight: 400 !important;
        letter-spacing: 0.04em;
        line-height: 1;
    }
</style>
