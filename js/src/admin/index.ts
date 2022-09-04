import app from "flarum/admin/app";

const trans = (key: string) => {
    return app.translator.trans(`nearata-dsts.admin.${key}`);
};

app.initializers.add("nearata-dsts", () => {
    app.extensionData
        .for("nearata-dsts")
        .registerSetting({
            setting: "nearata-dsts.admin.settings.hide_only_first_post",
            label: trans("settings.hide_only_first_post"),
            type: "boolean",
            help: trans("settings.hide_only_first_post_help"),
        })
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
        .registerSetting(() => {
            return m("h2", trans("settings.fof_upload.section_title"));
        })
        .registerSetting({
            setting: "nearata-dsts.admin.settings.hide_only_files",
            label: trans("settings.hide_only_files"),
            type: "boolean",
            help: trans("settings.hide_only_files_help"),
        })
        .registerSetting({
            setting: "nearata-dsts.admin.settings.fof_upload.require_like",
            label: trans("settings.fof_upload.require_like"),
            type: "boolean",
            help: trans("settings.fof_upload.require_like_help"),
        })
        .registerSetting({
            setting: "nearata-dsts.admin.settings.fof_upload.require_reply",
            label: trans("settings.fof_upload.require_reply"),
            type: "boolean",
        })
        .registerPermission(
            {
                icon: "fas fa-eye",
                label: trans("permissions.can_bypass_like"),
                permission: "nearata-dsts.bypass-like",
                // @ts-ignore
                tagScoped: true,
            },
            "view"
        )
        .registerPermission(
            {
                icon: "fas fa-eye",
                label: trans("permissions.can_bypass_reply"),
                permission: "nearata-dsts.bypass-reply",
                // @ts-ignore
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
                // @ts-ignore
                tagScoped: true,
            },
            "view"
        );
});
