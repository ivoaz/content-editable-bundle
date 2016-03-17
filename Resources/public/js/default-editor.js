(function () {
    var updateUrl = window.IVOAZ_CONTENT_EDITABLE_UPDATE_URL;
    var current, timeout;
    var htmlTags = document.getElementsByTagName('html');

    if (0 === htmlTags.length) {
        return;
    }

    var html = htmlTags[0];

    html.addEventListener('mousedown', onMouseDown);
    html.addEventListener('mouseup', onMouseUp);

    var contents = document.getElementsByClassName('ivoaz-content-editable');

    for (var i=0; i < contents.length; ++i) {
        contents[i].addEventListener('mousedown', onMouseDown);
    }

    function onMouseDown(event) {
        var target = event.currentTarget;

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
        request.open('PATCH', updateUrl.replace(':id', current.dataset.ivoazContentEditableId), true);
        request.setRequestHeader('Content-Type', 'application/json');
        request.setRequestHeader('Accept', 'application/json');
        request.send(JSON.stringify({ text: current.innerHTML }));

        current.contentEditable = false;
    }
})();
