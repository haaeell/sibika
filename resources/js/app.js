import './bootstrap';
import $ from 'jquery';
import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import select2 from 'select2';
import Swal from 'sweetalert2';
import flatpickr from 'flatpickr';
import Chart from 'chart.js/auto';
import NProgress from 'nprogress';

window.$ = window.jQuery = $;
if (typeof $.isArray !== 'function') {
    $.isArray = Array.isArray;
}
if (typeof $.trim !== 'function') {
    $.trim = (value) => value == null ? '' : String(value).trim();
}
select2(window, $);
window.Swal = Swal;
window.flatpickr = flatpickr;
window.Chart = Chart;
window.NProgress = NProgress;
DataTable.use($);
window.DataTable = DataTable;

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    },
});

window.initSelect2 = function (scope = document) {
    $(scope).find('.select2').each(function () {
        if (!$(this).hasClass('select2-hidden-accessible')) {
            $(this).select2({
                width: '100%',
                placeholder: $(this).data('placeholder') || 'Pilih data',
                allowClear: true,
            });
        }
    });
};

window.initRegionSelects = function (scope = document) {
    const province = $(scope).find('[data-region-select="province"]')[0];
    const city = $(scope).find('[data-region-select="city"]')[0];
    const district = $(scope).find('[data-region-select="district"]')[0];
    const village = $(scope).find('[data-region-select="village"]')[0];

    if (!province || !city || !district || !village) {
        return;
    }

    const endpoints = {
        province: '/regions/provinces',
        city: (code) => `/regions/regencies/${code}`,
        district: (code) => `/regions/districts/${code}`,
        village: (code) => `/regions/villages/${code}`,
    };

    const setLoading = function (select, loading) {
        $(select).prop('disabled', loading).trigger('change.select2');
    };

    const reset = function (select, placeholder) {
        $(select).empty().append(new Option(placeholder, '')).val('').prop('disabled', true).trigger('change');
    };

    const selectedCode = function (select) {
        return $(select).find('option:selected').data('region-code') || '';
    };

    const loadOptions = async function (select, url, placeholder, initialName = '', triggerChange = true) {
        setLoading(select, true);
        $(select).empty().append(new Option('Memuat...', ''));

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Wilayah gagal dimuat');

            const payload = await response.json();
            const items = payload.data || [];
            $(select).empty().append(new Option(placeholder, ''));
            items.forEach((item) => {
                const option = new Option(item.name, item.name);
                option.dataset.regionCode = item.code;
                $(select).append(option);
            });
            setLoading(select, false);

            let selected = items.find((item) => item.name === initialName);
            if (!selected && initialName) {
                const lowerInitial = initialName.toLowerCase();
                selected = items.find((item) => item.name.toLowerCase() === lowerInitial)
                    || items.find((item) => item.name.toLowerCase().includes(lowerInitial))
                    || items.find((item) => lowerInitial.includes(item.name.toLowerCase()));
            }
            if (selected) {
                $(select).val(selected.name);

                if (triggerChange) {
                    $(select).trigger('change');
                } else {
                    $(select).trigger('change.select2');
                }
            }

            return selected?.code || '';
        } catch (error) {
            $(select).empty().append(new Option('Gagal memuat data wilayah', '')).val('').prop('disabled', true).trigger('change');
            return '';
        }
    };

    reset(city, 'Pilih kabupaten/kota');
    reset(district, 'Pilih kecamatan');
    reset(village, 'Pilih kelurahan/desa');

    $(province).on('change', async function () {
        reset(district, 'Pilih kecamatan');
        reset(village, 'Pilih kelurahan/desa');

        if (selectedCode(this)) {
            await loadOptions(city, endpoints.city(selectedCode(this)), 'Pilih kabupaten/kota');
        } else {
            reset(city, 'Pilih kabupaten/kota');
        }
    });

    $(city).on('change', async function () {
        reset(village, 'Pilih kelurahan/desa');

        if (selectedCode(this)) {
            await loadOptions(district, endpoints.district(selectedCode(this)), 'Pilih kecamatan');
        } else {
            reset(district, 'Pilih kecamatan');
        }
    });

    $(district).on('change', function () {
        reset(village, 'Pilih kelurahan/desa');

        if (selectedCode(this)) {
            loadOptions(village, endpoints.village(selectedCode(this)), 'Pilih kelurahan/desa');
        }
    });

    const initialProvince = province.dataset.initial || '';
    const initialCity = city.dataset.initial || '';
    const initialDistrict = district.dataset.initial || '';
    const initialVillage = village.dataset.initial || '';

    loadOptions(province, endpoints.province, 'Pilih provinsi', initialProvince, false).then(async (provinceCode) => {
        if (!provinceCode) return;
        const cityCode = await loadOptions(city, endpoints.city(provinceCode), 'Pilih kabupaten/kota', initialCity, false);
        if (!cityCode) return;
        const districtCode = await loadOptions(district, endpoints.district(cityCode), 'Pilih kecamatan', initialDistrict, false);
        if (!districtCode) return;
        await loadOptions(village, endpoints.village(districtCode), 'Pilih kelurahan/desa', initialVillage, false);
    });
};

window.initBiodataProgress = function (scope = document) {
    $(scope).find('[data-biodata-progress]').each(function () {
        const $root = $(this);
        const $form = $root.find('[data-biodata-progress-form]');
        const $fields = $form.find('[data-progress-required]');

        if (!$fields.length) {
            return;
        }

        const fieldValue = function (field) {
            const $field = $(field);
            if ($field.is('[data-organization-name]') && $('[data-organization-status]').val() === 'tidak') {
                return '-';
            }
            const value = $field.val();

            if (String(value ?? '').trim() !== '') {
                return value;
            }

            return $field.data('progressInitialActive') === false
                ? ''
                : ($field.attr('data-initial') || $field.data('initial') || '');
        };

        const updateSection = function (section) {
            const $sectionFields = $fields.filter(`[data-progress-section="${section}"]`);
            const complete = $sectionFields.length > 0 && $sectionFields.toArray().every((field) => String(fieldValue(field)).trim() !== '');
            const $indicator = $root.find(`[data-progress-section-indicator="${section}"]`);

            $indicator.toggleClass('bg-emerald-50 text-emerald-700', complete);
            $indicator.toggleClass('bg-slate-50 text-slate-400', !complete);
            $indicator.find('i')
                .toggleClass('fa-circle-check text-emerald-500', complete)
                .toggleClass('fa-circle text-slate-300', !complete);
        };

        const update = function () {
            const completed = $fields.toArray().filter((field) => String(fieldValue(field)).trim() !== '').length;
            const total = $fields.length;
            const percentage = Math.round((completed / total) * 100);

            $root.find('[data-progress-percentage]').text(`${percentage}%`);
            $root.find('[data-progress-bar]').css('width', `${percentage}%`);
            $root.find('[data-progress-message]').text(
                percentage === 100
                    ? 'Semua data wajib sudah lengkap'
                    : `${completed} dari ${total} data wajib terisi`,
            );

            $root.find('[data-progress-section-indicator]').each(function () {
                updateSection($(this).data('progressSectionIndicator'));
            });
        };

        $fields.on('input change change.select2', function () {
            $(this).data('progressInitialActive', false);
            update();
        });

        $(document).on('change', '[data-organization-status]', update);
        $(document).on('change change.select2', '[data-region-select]', function () {
            setTimeout(update, 50);
        });

        update();
        setTimeout(update, 300);
        setTimeout(update, 1000);
    });
};

window.initDataTable = function (selector, options = {}) {
    return $(selector).DataTable({
        processing: true,
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Cari...',
            lengthMenu: '_MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Belum ada data',
            zeroRecords: 'Data tidak ditemukan',
            paginate: {
                previous: 'Sebelumnya',
                next: 'Selanjutnya',
            },
        },
        ...options,
    });
};

const appSwalOptions = {
    buttonsStyling: false,
    customClass: {
        popup: 'app-swal-popup',
        icon: 'app-swal-icon',
        title: 'app-swal-title',
        htmlContainer: 'app-swal-text',
        actions: 'app-swal-actions',
        confirmButton: 'app-swal-confirm',
        cancelButton: 'app-swal-cancel',
    },
    showClass: {
        popup: 'app-swal-show',
    },
    hideClass: {
        popup: 'app-swal-hide',
    },
};

const showAppSwal = function (options = {}) {
    return Swal.fire({
        ...appSwalOptions,
        ...options,
    });
};

window.confirmAction = function ({
    title = 'Apakah Anda yakin?',
    text = 'Aksi ini akan memproses data.',
    confirmText = 'Ya, lanjutkan',
    cancelText = 'Batal',
    icon = 'warning',
} = {}) {
    return showAppSwal({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true,
    });
};

window.showToast = function ({ icon = 'success', title = 'Data berhasil disimpan' } = {}) {
    return showAppSwal({
        icon,
        title,
        showConfirmButton: true,
        confirmButtonText: 'Mengerti',
    });
};

window.handleAjaxError = function (xhr) {
    const message = xhr.responseJSON?.message || 'Terjadi kesalahan. Silakan coba lagi.';

    return showAppSwal({
        icon: 'error',
        title: 'Gagal',
        text: message,
    });
};

window.setButtonLoading = function (button, loading, text = 'Memproses...') {
    const $button = $(button);

    if (loading) {
        $button.data('original-html', $button.html());
        $button.prop('disabled', true);
        $button.html(`<i class="fa-solid fa-spinner fa-spin"></i>${text}`);
        return;
    }

    $button.prop('disabled', false);
    $button.html($button.data('original-html'));
};

$(function () {
    initSelect2();
    initRegionSelects();
    initBiodataProgress();

    $('.datepicker').each(function () {
        flatpickr(this, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
        });
    });

    $('.datetimepicker').each(function () {
        flatpickr(this, {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'd M Y H:i',
            time_24hr: true,
        });
    });

    const desktopSidebarQuery = window.matchMedia('(min-width: 1024px)');
    const setDesktopSidebar = function (collapsed) {
        document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
        $('.js-sidebar-layout-toggle')
            .attr('aria-expanded', String(!collapsed))
            .attr('aria-label', collapsed ? 'Buka menu' : 'Tutup menu');
    };
    const closeMobileSidebar = function () {
        $('[data-sidebar-overlay], [data-sidebar-drawer]').addClass('hidden');
        $('body').removeClass('overflow-hidden');
        $('.js-sidebar-layout-toggle').attr('aria-expanded', 'false');
    };

    const syncSidebarForViewport = function () {
        if (desktopSidebarQuery.matches) {
            closeMobileSidebar();
            setDesktopSidebar(localStorage.getItem('sidebarCollapsed') === 'true');
            return;
        }

        $('.js-sidebar-layout-toggle').attr('aria-expanded', 'false').attr('aria-label', 'Buka menu');
    };

    syncSidebarForViewport();
    desktopSidebarQuery.addEventListener('change', syncSidebarForViewport);

    $('.js-sidebar-layout-toggle').on('click', function () {
        if (desktopSidebarQuery.matches) {
            const collapsed = !document.documentElement.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', String(collapsed));
            setDesktopSidebar(collapsed);
            return;
        }

        $('[data-sidebar-overlay], [data-sidebar-drawer]').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        $(this).attr('aria-expanded', 'true');
    });

    $('[data-sidebar-overlay], .js-sidebar-close').on('click', closeMobileSidebar);

    $(document).on('keydown', function (event) {
        if (event.key === 'Escape' && !desktopSidebarQuery.matches) {
            closeMobileSidebar();
        }
    });

    const setAcademicYear = function (year) {
        $('.js-academic-year-label').text(year);
        $('.js-academic-year-option').each(function () {
            const isSelected = $(this).data('academic-year') === year;

            $(this).toggleClass('bg-blue-50 text-blue-900', isSelected);
            $(this).find('[data-academic-year-check]').toggleClass('hidden', !isSelected);
        });
    };

    setAcademicYear(localStorage.getItem('academicYear') || '2026 / 2027');

    $('.js-academic-year-toggle').on('click', function (event) {
        event.stopPropagation();

        const $button = $(this);
        const $menu = $('#' + $button.attr('aria-controls'));
        const isExpanded = $button.attr('aria-expanded') === 'true';

        $button.attr('aria-expanded', String(!isExpanded));
        $menu.toggleClass('hidden', isExpanded);
    });

    $('.js-academic-year-option').on('click', function () {
        const year = $(this).data('academic-year');

        localStorage.setItem('academicYear', year);
        setAcademicYear(year);
        $('[data-academic-year-menu]').addClass('hidden');
        $('.js-academic-year-toggle').attr('aria-expanded', 'false');
    });

    $('.js-user-menu-toggle').on('click', function (event) {
        event.stopPropagation();

        const $button = $(this);
        const $menu = $('#' + $button.attr('aria-controls'));
        const isExpanded = $button.attr('aria-expanded') === 'true';

        $button.attr('aria-expanded', String(!isExpanded));
        $menu.toggleClass('hidden', isExpanded);
    });

    $(document).on('submit', '.js-delete-form', function (event) {
        event.preventDefault();

        const form = this;

        confirmAction({
            title: form.dataset.confirmTitle || 'Hapus data?',
            text: form.dataset.confirmText || 'Data yang dihapus tidak bisa dikembalikan.',
            confirmText: form.dataset.confirmButton || 'Ya, hapus',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).on('submit', '.js-confirm-form', function (event) {
        event.preventDefault();

        const form = this;

        confirmAction({
            title: form.dataset.confirmTitle || 'Proses data?',
            text: form.dataset.confirmText || 'Pastikan data sudah benar.',
            confirmText: form.dataset.confirmButton || 'Ya, lanjutkan',
            icon: form.dataset.confirmIcon || 'warning',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.js-academic-year-toggle, [data-academic-year-menu]').length) {
            $('[data-academic-year-menu]').addClass('hidden');
            $('.js-academic-year-toggle').attr('aria-expanded', 'false');
        }

        if (!$(event.target).closest('.js-user-menu-toggle, [data-user-menu]').length) {
            $('[data-user-menu]').addClass('hidden');
            $('.js-user-menu-toggle').attr('aria-expanded', 'false');
        }
    });

    $('.js-toggle-password').on('click', function () {
        const $button = $(this);
        const $input = $button.siblings('input');
        const isPassword = $input.attr('type') === 'password';

        $input.attr('type', isPassword ? 'text' : 'password');
        $button.attr('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        $button.find('i').toggleClass('fa-eye fa-eye-slash');
    });

    $('.js-sidebar-toggle').on('click', function () {
        const $button = $(this);
        const $panel = $('#' + $button.attr('aria-controls'));
        const isExpanded = $button.attr('aria-expanded') === 'true';

        $button.attr('aria-expanded', String(!isExpanded));
        $panel.toggleClass('hidden', isExpanded);
        $button.find('[data-sidebar-chevron]').toggleClass('-rotate-90', isExpanded);
    });
});
