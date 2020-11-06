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

        let customFieldValues = {};

        if (customFields.titles && customFields.values) {

            for (let i = 0; i < customFields.titles.length; i++) {

                if (!(customFields.titles[i] && customFields.values[i])) {
                    continue;
                }

                const title = $(customFields.titles[i]).val() ?? null;
                const value = $(customFields.values[i]).val() ?? null;

                if (!value) { continue; }

                customFieldValues[i] = {
                    title: title,
                    value: value
                }

            }
        }

        if (isPriority) { href = href + '&isPriority'; }

        if (customFieldValues) {
            href += '&customFields=' + JSON.stringify(customFieldValues);
        }

        return window.open(href);
    });

});