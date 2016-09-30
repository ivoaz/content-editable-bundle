(function () {
    var updateUrl = window.IVOAZ_CONTENT_EDITABLE_UPDATE_URL;
    var current, timeout;
    var htmlTags = document.getElementsByTagName('html');

    if (0 === htmlTags.length) {
        return;
    }

    document.addEventListener('mouseup', onMouseUp);
    document.addEventListener('mousedown', function (event) {
        var el = event.target
            , found;

        while (el && !(found = el.classList.contains('ivoaz-content-editable'))) {
            el = el.parentElement;
        }

        onMouseDown(event, found ? el : undefined);
    });

    function onMouseDown(event, target) {
        target = target || event.currentTarget;

        if (current && current !== target) {
            save();
            current = undefined;
        }

        if (!target.classList.contains('ivoaz-content-editable')) {
            return;
        }

        event.stopPropagation();

        if (current) {
            return;
        }

        timeout = setTimeout(function () {
            current = target;
            edit();
        }, 1000);
    }

    function onMouseUp(event) {
        if (timeout) {
            clearTimeout(timeout);
            timeout = undefined;
        }
    }

    function edit() {
        current.contentEditable = true;
        current.focus();
    }

    function save() {
        var request = new XMLHttpRequest();
        request.open('PUT', updateUrl.replace(':id', current.dataset.ivoazContentEditableId), true);
        request.setRequestHeader('Content-Type', 'application/json');
        request.setRequestHeader('Accept', 'application/json');
        request.send(JSON.stringify({ text: current.innerHTML }));

        current.contentEditable = false;
    }
})();
