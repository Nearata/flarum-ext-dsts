import app from "flarum/admin/app";

const trans = (key: string) => {
    return app.translator.trans(`nearata-dsts.admin.${key}`);
};

app.initializers.add("nearata-dsts", () => {
    app.extensionData
        .for("nearata-dsts")
        .registerSetting({
            setting: "nearata-dsts.admin.settings.require_like",
            label: trans("settings.require_like"),
            type: "boolean",
            help: trans("settings.require_like_help"),
        })
        .registerSetting({
            setting: "nearata-dsts.admin.settings.require_reply",
            label: trans("settings.require_reply"),
            type: "boolean",
        })
        .registerPermission(
            {
                icon: "fas fa-eye",
                label: trans("permissions.can_bypass_like"),
                permission: "nearata-dsts.bypass-like",
                tagScoped: true,
            },
            "view"
        )
        .registerPermission(
            {
                icon: "fas fa-eye",
                label: trans("permissions.can_bypass_reply"),
                permission: "nearata-dsts.bypass-reply",
                tagScoped: true,
            },
            "view"
        )
        .registerPermission(
            {
                icon: "fas fa-eye",
                label: trans("permissions.can_bypass_login"),
                permission: "nearata-dsts.bypass-login",
                allowGuest: true,
                tagScoped: true,
            },
            "view"
        );
});
