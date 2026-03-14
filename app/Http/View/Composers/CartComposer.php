<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;

class CartComposer
{
    /**
     * Bind cart count to navigation and bottom-nav views.
     */
    public function compose(View $view): void
    {
        $cart = session()->get('cart', []);
        $cartCount = $cart ? array_sum(array_column($cart, 'quantity')) : 0;

        $view->with('cartCount', $cartCount);
    }
}
