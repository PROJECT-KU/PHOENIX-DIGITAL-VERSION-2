<form wire:submit.prevent="search" class="search-form desktop-search-form w-50">
    <div class="input-group">
        <input
            type="text"
            wire:model="searchQuery"
            class="form-control"
            placeholder="Search for products...">
        <button class="btn" type="submit">
            <i class="bi bi-search"></i>
        </button>
    </div>
</form>