<div class="lemon-logo">
    <svg viewBox="0 0 120 120" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Lemon">
        <defs>
            <linearGradient id="peel" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#fde047"/><stop offset="1" stop-color="#eab308"/>
            </linearGradient>
            <radialGradient id="flesh" cx="42%" cy="38%" r="70%">
                <stop offset="0" stop-color="#fef9c3"/><stop offset="1" stop-color="#fde68a"/>
            </radialGradient>
        </defs>
        <g class="lemon-spin">
            <g class="lemon-pulse">
                <circle cx="60" cy="60" r="54" fill="url(#peel)"/>
                <circle cx="60" cy="60" r="47" fill="#fffef2"/>
                <circle cx="60" cy="60" r="41" fill="url(#flesh)"/>
                @for ($i = 0; $i < 10; $i++)
                <line x1="60" y1="60" x2="60" y2="20" stroke="#fffef2" stroke-width="3.4" stroke-linecap="round"
                      transform="rotate({{ $i * 36 }} 60 60)"/>
                @endfor
                <circle cx="60" cy="60" r="6" fill="#fffef2"/>
                <ellipse cx="60" cy="34" rx="3" ry="5" fill="#fde68a" stroke="#eab308" stroke-width="1"/>
                <ellipse cx="82" cy="52" rx="3" ry="5" fill="#fde68a" stroke="#eab308" stroke-width="1" transform="rotate(60 82 52)"/>
                <ellipse cx="44" cy="42" rx="16" ry="10" fill="#ffffff" opacity=".28" transform="rotate(-30 44 42)"/>
            </g>
        </g>
    </svg>
</div>
