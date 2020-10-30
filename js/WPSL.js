jQuery(document).ready($ => {

    $('#WPSL_submit').on('click', (e) => {

        e.preventDefault();

        const isPriority = $('#WPSL_isPriority').is(':checked') ?? null;

        const customFields = [
            {
                title: $('#WPSL_customField1Title').val() ?? null,
                value: $('#WPSL_customField1Value').val() ?? null
            },
            {
                title: $('#WPSL_customField2Title').val() ?? null,
                value: $('#WPSL_customField2Value').val() ?? null
            },
            {
                title: $('#WPSL_customField3Title').val() ?? null,
                value: $('#WPSL_customField3Value').val() ?? null
            }

        ]

        let href = $('#WPSL_href').val() ?? null;

        if (!href) { return; }

        if (isPriority) { href = href + '&isPriority'; }

        for (let i = 0; i < customFields.length; i++) {

            if (customFields[i].title) {
                href = href + '&customField' + (i + 1) + 'Title=' + customFields[i].title;
            }

            if (customFields[i].value) {
                href = href + '&customField'+ (i + 1) + 'Value=' + customFields[i].value;
            }

        }

        return window.location.href = href;
    });

});