{{-- Blade's compiler mis-handles a literal <? ... ?> sequence even inside a quoted
     string, so the declaration is built from concatenated pieces to avoid it. --}}
{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
