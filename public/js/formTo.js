let ldn = {
    "@context": [
        "https://www.w3.org/ns/activitystreams",
        "https://purl.org/coar/notify"
    ],
    "actor": {
        "id": "https://overlay-journal.com",
        "name": "Overlay Journal",
        "type": "Service"
    },
    "context": {
        "id": "https://research-organisation.org/repository/preprint/201203/421/",
        "ietf:cite-as": "https://doi.org/10.5555/12345680",
        "type": "sorg:AboutPage",
        "url": {
            "id": "https://research-organisation.org/repository/preprint/201203/421/content.pdf",
            "media-type": "application/pdf",
            "type": [
                "Article",
                "sorg:ScholarlyArticle"
            ]
        }
    },
    "id": "urn:uuid:94ecae35-dcfd-4182-8550-22c7164fe23f",
    "object": {
        "id": "https://overlay-journal.com/reviews/000001/00001",
        "ietf:cite-as": "https://doi.org/10.3214/987654",
        "type": [
            "Document",
            "sorg:Review"
        ]
    },
    "origin": {
        "id": "https://overlay-journal.com/system",
        "inbox": "https://overlay-journal.com/system/inbox/",
        "type": "Service"
    },
    "target": {
        "id": "https://research-organisation.org/repository",
        "inbox": "https://research-organisation.org/repository/inbox/",
        "type": "Service"
    },
    "type": [
        "Announce",
        "coar-notify:ReviewAction"
    ]
};

function getProp(name) {
    var name = name.split("_");

    if (name.length === 3)
        return ldn[name[0]][name[1]][name[2]]
    else if (name.length === 2)
        return ldn[name[0]][name[1]]
    else
        return ldn[name]
}

function setProp(name, value) {
    var name = name.split("_");

    if (name.length === 3) {
        if (name[2] === 'type')
            value = value.split(',')

        ldn[name[0]][name[1]][name[2]] = value;
    } else if (name.length === 2) {
        if (name[0] === 'object' && name[1] === 'type')
            value = value.split(',')

        ldn[name[0]][name[1]] = value;
    } else {
        if (name[0] === 'type')
            value = value.split(',')

        ldn[name] = value;
    }
}

$(document).ready(function () {
    $(":input[type=text]").each(function () {
        this.name = this.id;
        this.size = 34;
        this.value = getProp(this.name)
        this.onkeyup = function () {
            setProp(this.name, this.value);
            $("#preview").text(JSON.stringify(ldn, null, 2));
        }
    });

    $("#preview").text(JSON.stringify(ldn, null, 2));
});
