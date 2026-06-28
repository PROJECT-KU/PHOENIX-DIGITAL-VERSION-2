<?php

namespace App\Livewire\Pages\Admin\Order;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    public string $activeTab = 'all';

    public string $search = '';

    public string $filterMonth = '';

    public string $filterYear = '';

    protected $queryString = ['activeTab'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMonth(): void
    {
        $this->resetPage();
    }

    public function updatedFilterYear(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterMonth', 'filterYear']);
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        return Order::query()
            ->with('customer', 'items')
            ->when($this->search, function ($q) {
                $term = $this->search;
                $q->where(function ($sub) use ($term) {
                    $sub->where('order_number', 'like', "%{$term}%")
                        ->orWhere('status', 'like', "%{$term}%")
                        ->orWhere('payment_method', 'like', "%{$term}%")
                        ->orWhere('customer_notes', 'like', "%{$term}%")
                        ->orWhereHas('customer', function ($c) use ($term) {
                            $c->where('nama', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%")
                                ->orWhere('no_hp', 'like', "%{$term}%");
                        })
                        ->orWhereHas('items', function ($it) use ($term) {
                            $it->where('product_name', 'like', "%{$term}%")
                                ->orWhere('account_username', 'like', "%{$term}%")
                                ->orWhere('account_link', 'like', "%{$term}%")
                                ->orWhere('subscription_status', 'like', "%{$term}%")
                                ->orWhere('delivery_status', 'like', "%{$term}%");
                        });
                });
            })
            ->when($this->activeTab === 'processing', function ($q) {
                $q->where('status', 'processing');
            })
            ->when($this->activeTab === 'completed', function ($q) {
                $q->where('status', 'completed');
            })
            ->when($this->activeTab === 'neworder', function ($q) {
                $q->where('status', 'pending');
            })
            ->when($this->activeTab === 'cancelled', function ($q) {
                $q->where('status', 'cancelled');
            })
            ->when($this->filterMonth, function ($q) {
                $q->whereMonth('created_at', $this->filterMonth);
            })
            ->when($this->filterYear, function ($q) {
                $q->whereYear('created_at', $this->filterYear);
            })
            ->latest()
            ->paginate(10);
    }

    // Tab "Akun Habis" menampilkan ITEM yang habis (bukan order),
    // karena satu order bisa terdiri dari beberapa item dengan masa aktif berbeda.
    public function getHabisItemsProperty()
    {
        return OrderItem::query()
            ->with('order.customer', 'product')
            ->where(function ($q) {
                $q->where('subscription_status', 'habis')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('end_date')
                            ->where('end_date', '<', now());
                    });
            })
            ->when($this->search, function ($q) {
                $term = $this->search;
                $q->where(function ($sub) use ($term) {
                    $sub->where('product_name', 'like', "%{$term}%")
                        ->orWhere('account_username', 'like', "%{$term}%")
                        ->orWhere('account_link', 'like', "%{$term}%")
                        ->orWhere('subscription_status', 'like', "%{$term}%")
                        ->orWhereHas('order', function ($o) use ($term) {
                            $o->where('order_number', 'like', "%{$term}%")
                                ->orWhereHas('customer', function ($c) use ($term) {
                                    $c->where('nama', 'like', "%{$term}%")
                                        ->orWhere('email', 'like', "%{$term}%")
                                        ->orWhere('no_hp', 'like', "%{$term}%");
                                });
                        });
                });
            })
            ->when($this->filterMonth || $this->filterYear, function ($q) {
                $q->whereHas('order', function ($q2) {
                    $q2->when($this->filterMonth, fn ($x) => $x->whereMonth('created_at', $this->filterMonth))
                        ->when($this->filterYear, fn ($x) => $x->whereYear('created_at', $this->filterYear));
                });
            })
            ->orderBy('end_date', 'desc')
            ->paginate(10);
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $habisItemsCount = OrderItem::where('subscription_status', 'habis')
            ->orWhere(function ($q) {
                $q->whereNotNull('end_date')->where('end_date', '<', now());
            })
            ->count();

        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM'),
        ]);

        $years = Order::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.pages.admin.order.order-list', [
            'orders' => $this->activeTab === 'habis' ? null : $this->orders,
            'habisItems' => $this->activeTab === 'habis' ? $this->habisItems : null,
            'months' => $months,
            'years' => $years,
            'tabCounts' => [
                'all' => Order::count(),
                'neworder' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
                'habis' => $habisItemsCount,
            ],
        ]);
    }
}
