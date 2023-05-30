import app from "flarum/admin/app";

const trans = (key: string) => {
  return app.translator.trans(`nearata-dsts.admin.${key}`);
};

app.initializers.add("nearata-dsts", () => {
  const extensionData = app.extensionData
    .for("nearata-dsts")
    .registerSetting({
      setting: "nearata-dsts.admin.settings.enabled",
      label: trans("settings.enabled"),
      type: "boolean",
    })
    .registerSetting({
      setting: "nearata-dsts.admin.settings.hide_only_first_post",
      label: trans("settings.hide_only_first_post"),
      type: "boolean",
    })
    .registerSetting({
      setting: "nearata-dsts.admin.settings.require_reply",
      label: trans("settings.require_reply"),
      type: "boolean",
    })
    // permissions
    .registerPermission(
      {
        icon: "fas fa-eye",
        label: trans("permissions.can_bypass_login"),
        permission: "discussion.nearata-dsts.bypass-login",
        allowGuest: true,
      },
      "view"
    )
    .registerPermission(
      {
        icon: "fas fa-eye",
        label: trans("permissions.can_bypass_reply"),
        permission: "discussion.nearata-dsts.bypass-reply",
      },
      "view"
    )
    .registerPermission(
      {
        icon: "fas fa-eye",
        label: trans("permissions.can_bypass_like"),
        permission: "discussion.nearata-dsts.bypass-like",
      },
      "view"
    )
    .registerPermission(
      {
        icon: "fas fa-tag",
        label: trans("permissions.can_use_bbcode"),
        permission: "nearata-dsts.can-use-bbcode",
      },
      "start"
    );

  if ("flarum-likes" in flarum.extensions) {
    extensionData.registerSetting({
      setting: "nearata-dsts.admin.settings.require_like",
      label: trans("settings.require_like"),
      type: "boolean",
    });
  }

  if ("fof-upload" in flarum.extensions) {
    extensionData
      .registerSetting(() => {
        return <h2>{trans("settings.fof_upload.section_title")}</h2>;
      })
      .registerSetting({
        setting: "nearata-dsts.admin.settings.fof_upload.enabled",
        label: trans("settings.fof_upload.enabled"),
        type: "boolean",
      })
      .registerSetting({
        setting: "nearata-dsts.admin.settings.fof_upload.require_reply",
        label: trans("settings.fof_upload.require_reply"),
        type: "boolean",
      });

    if ("flarum-likes" in flarum.extensions) {
      extensionData.registerSetting({
        setting: "nearata-dsts.admin.settings.fof_upload.require_like",
        label: trans("settings.fof_upload.require_like"),
        type: "boolean",
      });
    }
  }
});
