import Application from "flarum/common/Application";
import Button from "flarum/common/components/Button";
import TextEditor from "flarum/common/components/TextEditor";
import Tooltip from "flarum/common/components/Tooltip";
import { extend } from "flarum/common/extend";
import icon from "flarum/common/helpers/icon";
import app from "flarum/forum/app";
import CommentPost from "flarum/forum/components/CommentPost";

class MyTooltip extends Tooltip {
  onupdate(_: any): void {
    // no need to recreate, update it with the new text
    if (this.oldText === this.attrs.text) {
      this.childDomNode.setAttribute("aria-label", this.attrs.text);
      this.childDomNode.dataset.originalTitle = this.attrs.text;
    }
  }
}

app.initializers.add("nearata-dsts", () => {
  // realtime
  extend(Application.prototype, "request", function (promise, originalOptions) {
    if (app.session.user?.isAdmin()) {
      return;
    }

    const apiUrl = app.forum.attribute("apiUrl");
    const isPost = originalOptions.url.startsWith(`${apiUrl}/posts`);

    const flag = originalOptions.method === "POST" && isPost;
    const flag1 =
      originalOptions.method === "PATCH" &&
      isPost &&
      ("isHidden" in originalOptions.body.data.attributes ||
        "isLiked" in originalOptions.body.data.attributes);

    if (flag || flag1) {
      promise
        .then(async (r: any) => {
          const id = r.data.relationships.discussion.data.id;

          await app.store.find("discussions", id);
        })
        .finally(m.redraw);
    }
  });

  extend(TextEditor.prototype, "controlItems", function (items) {
    if (!app.session.user?.attribute("canNearataDstsUseBbcode")) {
      return;
    }

    items.add(
      "nearataDsts",
      <Tooltip
        text={app.translator.trans("nearata-dsts.forum.composer.dsts_tooltip")}
      >
        <Button
          class="Button Button--icon"
          icon="fas fa-eye-slash"
          onclick={() => {
            this.attrs.composer.editor.insertAtCursor(
              '[nearata-dsts login="true" like="false" reply="false"][/nearata-dsts]'
            );
          }}
        ></Button>
      </Tooltip>
    );
  });

  extend(CommentPost.prototype, "headerItems", function (items) {
    const error = this.attrs.post.attribute<string | null>("nearataDstsError");

    if (!error) {
      return;
    }

    items.add(
      "nearataDsts",
      <MyTooltip text={error}>{icon("fas fa-eye-slash")}</MyTooltip>
    );
  });

  extend(CommentPost.prototype, "elementAttrs", function (attrs) {
    if (this.headerItems().has("nearataDsts")) {
      attrs.className += " NearataDsts";
    }
  });

  extend(CommentPost.prototype, ["oncreate", "onupdate"], function () {
    const error = this.attrs.post.attribute<string | null>(
      "nearataDstsErrorFofUpload"
    );

    const element = this.element.querySelector(".NearataDstsFofUpload");
    const errorElement = element?.querySelector(".NearataDstsError");

    if (error) {
      element?.classList.add("on");

      if (errorElement) {
        errorElement.innerHTML = error;
      }
    } else {
      element?.classList.remove("on");

      if (errorElement) {
        errorElement.innerHTML = "";
      }
    }
  });
});
