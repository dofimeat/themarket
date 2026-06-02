/**
 * Переиндексация строк размеров в форме товара (ProductAddForm / ProductEditForm).
 */
(function () {
    function reindexSizeRows(wrap, formName) {
        if (!wrap || !formName) {
            return;
        }
        wrap.querySelectorAll('.seller-edit-size-row').forEach(function (row, idx) {
            var idInp = row.querySelector('[data-size-id]');
            var sizeInp = row.querySelector('[data-size-label]');
            var qtyInp = row.querySelector('[data-size-qty]');
            if (idInp) {
                idInp.name = formName + '[sizes][' + idx + '][id]';
            }
            if (sizeInp) {
                sizeInp.name = formName + '[sizes][' + idx + '][size]';
            }
            if (qtyInp) {
                qtyInp.name = formName + '[sizes][' + idx + '][quantity]';
            }
        });
    }

    function reindexFeatureRows(wrap, formName) {
        if (!wrap || !formName) {
            return;
        }
        wrap.querySelectorAll('.seller-edit-feature-row').forEach(function (row, idx) {
            var idInp = row.querySelector('[data-feature-id]');
            var nameInp = row.querySelector('[data-feature-name]');
            var valueInp = row.querySelector('[data-feature-value]');
            if (idInp) {
                idInp.name = formName + '[features][' + idx + '][id]';
            }
            if (nameInp) {
                nameInp.name = formName + '[features][' + idx + '][name]';
            }
            if (valueInp) {
                valueInp.name = formName + '[features][' + idx + '][value]';
            }
        });
    }

    window.initSellerProductSizes = function (options) {
        var wrap = document.getElementById(options.wrapId || 'seller-edit-sizes');
        var addBtn = document.getElementById(options.addBtnId || 'seller-edit-add-size');
        var formName = options.formName;
        if (!wrap || !formName) {
            return;
        }

        reindexSizeRows(wrap, formName);

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var rows = wrap.querySelectorAll('.seller-edit-size-row');
                var tpl = rows.length ? rows[rows.length - 1].cloneNode(true) : null;
                if (!tpl) {
                    return;
                }
                tpl.querySelectorAll('input').forEach(function (inp) {
                    if (inp.hasAttribute('data-size-id')) {
                        inp.value = '';
                    } else if (inp.hasAttribute('data-size-qty')) {
                        inp.value = '1';
                    } else if (inp.hasAttribute('data-size-label')) {
                        inp.value = '';
                    }
                });
                wrap.appendChild(tpl);
                reindexSizeRows(wrap, formName);
            });
        }

        wrap.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove-size]');
            if (!btn) {
                return;
            }
            var row = btn.closest('.seller-edit-size-row');
            if (!row) {
                return;
            }
            if (wrap.querySelectorAll('.seller-edit-size-row').length <= 1) {
                row.querySelectorAll('input').forEach(function (inp) {
                    if (inp.hasAttribute('data-size-id')) {
                        inp.value = '';
                    } else if (inp.hasAttribute('data-size-qty')) {
                        inp.value = '1';
                    } else {
                        inp.value = '';
                    }
                });
                return;
            }
            row.remove();
            reindexSizeRows(wrap, formName);
        });
    };

    window.initSellerProductFeatures = function (options) {
        var wrap = document.getElementById(options.wrapId || 'seller-edit-features');
        var addBtn = document.getElementById(options.addBtnId || 'seller-edit-add-feature');
        var formName = options.formName;
        if (!wrap || !formName) {
            return;
        }

        reindexFeatureRows(wrap, formName);

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var rows = wrap.querySelectorAll('.seller-edit-feature-row');
                var tpl = rows.length ? rows[rows.length - 1].cloneNode(true) : null;
                if (!tpl) {
                    return;
                }
                tpl.querySelectorAll('input').forEach(function (inp) {
                    if (inp.hasAttribute('data-feature-id')) {
                        inp.value = '';
                    } else if (inp.hasAttribute('data-feature-name')) {
                        inp.value = '';
                    } else if (inp.hasAttribute('data-feature-value')) {
                        inp.value = '';
                    }
                });
                wrap.appendChild(tpl);
                reindexFeatureRows(wrap, formName);
            });
        }

        wrap.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove-feature]');
            if (!btn) {
                return;
            }
            var row = btn.closest('.seller-edit-feature-row');
            if (!row) {
                return;
            }
            if (wrap.querySelectorAll('.seller-edit-feature-row').length <= 1) {
                row.querySelectorAll('input').forEach(function (inp) {
                    if (inp.hasAttribute('data-feature-id')) {
                        inp.value = '';
                    } else {
                        inp.value = '';
                    }
                });
                return;
            }
            row.remove();
            reindexFeatureRows(wrap, formName);
        });
    };
})();
