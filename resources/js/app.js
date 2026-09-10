import './bootstrap';
import $ from 'jquery';
import 'datatables.net-dt';
import 'datatables.net-responsive-dt';
import 'select2';
import Swal from 'sweetalert2';
import flatpickr from 'flatpickr';
import Chart from 'chart.js/auto';
import NProgress from 'nprogress';

import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-responsive-dt/css/responsive.dataTables.css';
import 'select2/dist/css/select2.css';
import 'sweetalert2/dist/sweetalert2.css';
import '@fortawesome/fontawesome-free/css/all.css';
import 'flatpickr/dist/flatpickr.css';
import 'nprogress/nprogress.css';

window.$ = window.jQuery = $;
window.Swal = Swal;
window.flatpickr = flatpickr;
window.Chart = Chart;
window.NProgress = NProgress;

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

window.confirmAction = function ({
    title = 'Apakah Anda yakin?',
    text = 'Aksi ini akan memproses data.',
    confirmText = 'Ya, lanjutkan',
    cancelText = 'Batal',
    icon = 'warning',
} = {}) {
    return Swal.fire({
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
    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
    });
};

window.handleAjaxError = function (xhr) {
    const message = xhr.responseJSON?.message || 'Terjadi kesalahan. Silakan coba lagi.';

    return Swal.fire({
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

    $('.js-sidebar-open').on('click', function () {
        $('[data-sidebar-overlay], [data-sidebar-drawer]').removeClass('hidden');
    });

    $('[data-sidebar-overlay]').on('click', function () {
        $('[data-sidebar-overlay], [data-sidebar-drawer]').addClass('hidden');
    });

    const setAcademicYear = function (year) {
        $('.js-academic-year-label').text(year);
        $('.js-academic-year-option').each(function () {
            const isSelected = $(this).data('academic-year') === year;

            $(this).toggleClass('bg-indigo-50 text-indigo-700', isSelected);
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

    $(document).on('click', function (event) {
        if (!$(event.target).closest('.js-academic-year-toggle, [data-academic-year-menu]').length) {
            $('[data-academic-year-menu]').addClass('hidden');
            $('.js-academic-year-toggle').attr('aria-expanded', 'false');
        }
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
