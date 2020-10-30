jQuery(document).ready($ => {

    $('#WPSL_submit').on('click', (e) => {

        e.preventDefault();

        let href = $('#WPSL_href').val() ?? null;

        if (!href) { return; }

        const isPriority = $('#WPSL_isPriority').is(':checked') ?? null;

        const customFields = {
            titles: $('.WPSL_customFieldTitle') ?? null,
            values: $('.WPSL_customFieldValue') ?? null
        };

        let customFieldValues = [];

        if (customFields.titles && customFields.values) {

            customFields.titles.each(() => {
                console.debug($(this).val());
            })

            return;
        }

        if (isPriority) { href = href + '&isPriority'; }

        for (let i = 0; i < customFields.length; i++) {

            if (customFields[i].title) {
                href = href + '&customField' + (i + 1) + 'Title=' + customFields[i].title;
            }

            if (customFields[i].value) {
                href = href + '&customField'+ (i + 1) + 'Value=' + customFields[i].value;
            }

        }

        return window.open(href);
    });

});