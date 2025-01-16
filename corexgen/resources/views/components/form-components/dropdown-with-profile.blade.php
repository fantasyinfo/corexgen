@props([
    'title' => 'Select',
    'options' => [],
    'name' => 'assign_to',
    'multiple' => false,
    'selected' => null,
    'required' => false,
])
  <x-form-components.create-new :link="'users.create'" :text="'Create new'" />
<div class="dropdown custom-select">
    <button class="btn btn-secondary dropdown-toggle w-100 customDropdownProfile" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $title }}
    </button>

    <div class="dropdown-menu w-100">
        <div class="px-3 pb-2">
            <input type="text" class="form-control search-input" placeholder="Search...">
        </div>

        <div class="dropdown-items" style="max-height: 300px; overflow-y: auto;">
            @foreach ($options as $option)
                <div class="dropdown-item">
                    <div class="form-check d-flex align-items-center gap-2">
                        <input type="{{ $multiple ? 'checkbox' : 'radio' }}" class="form-check-input"
                            name="{{ $name }}{{ $multiple ? '[]' : '' }}" value="{{ $option->id }}"
                            id="_{{ $name }}_{{ $option->id }}" {{ $required ? 'required' : '' }}
                            {{ isset($selected) && (is_array($selected) ? in_array($option->id, $selected) : $selected == $option->id) ? 'checked' : '' }}>
                     
                        <label class="form-check-label" for="_{{ $name }}_{{ $option->id }}">
                            <x-form-components.profile-avatar :url="$option->profile_photo_url" :hw="35" />
                            {{ $option->name }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('style')
    <style>
        .customDropdownProfile {
            background-color: var(--input-bg);
            border-color: var(--input-border);
            color: var(--body-color) !important;
        }

        .dropdown-items {
            max-height: 300px; /* Limit height for large dropdowns */
            overflow-y: auto; /* Enable scrolling */
        }

        .search-input {
            margin-bottom: 0.5rem; /* Spacing between search input and list */
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.custom-select').forEach(select => {
                const searchInput = select.querySelector('.search-input');
                const items = select.querySelectorAll('.dropdown-item');

                // Search functionality
                searchInput.addEventListener('input', (e) => {
                    const searchValue = e.target.value.toLowerCase().trim(); // Trim spaces for better search handling
                    items.forEach(item => {
                        const labelText = item.querySelector('label').textContent.toLowerCase();
                        if (labelText.includes(searchValue)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });

                // Prevent dropdown from closing when clicking an input
                select.querySelectorAll('input[type="checkbox"], input[type="radio"], label').forEach(input => {
                    input.addEventListener('click', (e) => {
                        e.stopPropagation(); // Keep dropdown open
                    });
                });
            });
        });
    </script>
@endpush
