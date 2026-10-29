(() => {
  const requests = new Map();
  const instances = new WeakMap();

  function load(nav) {
    const { treeUrl, treeNonce } = nav.dataset;
    const key = `${treeUrl}:${treeNonce}`;
    if (!requests.has(key)) {
      requests.set(
        key,
        fetch(treeUrl, {
          credentials: "same-origin",
          headers: treeNonce ? { "X-WP-Nonce": treeNonce } : {},
          signal: AbortSignal.timeout(15000),
        }).then((response) => {
          if (!response.ok) throw new Error("Navigation unavailable");
          return response.json();
        }),
      );
    }
    return requests.get(key);
  }

  class Tree {
    constructor(nav) {
      this.nav = nav;
      this.items = [];
      this.last = null;
      this.search = "";
      this.searchTime = 0;
      this.started = false;
    }

    focus() {
      const reserve = this.nav.querySelector("[data-tree-reserve]");
      (
        this.last ??
        reserve?.querySelector('[aria-current="page"]') ??
        reserve?.querySelector("a")
      )?.focus();
    }

    async start() {
      if (this.started) return;
      this.started = true;
      const status = this.nav.querySelector('[role="status"]');
      const indicator = this.nav
        .closest("[data-tree-dialog-content]")
        ?.querySelector("[data-tree-loading]");
      const reserve = this.nav.querySelector("[data-tree-reserve]");
      status.hidden = false;
      indicator?.classList.add("is-loading");
      this.nav.setAttribute("aria-busy", "true");
      try {
        const data = await load(this.nav);
        if (!Array.isArray(data) || data.length === 0)
          throw new Error("Empty navigation");
        const currentKey = reserve.querySelector('[aria-current="page"]')
          ?.dataset.treeKey;
        const tree = this.build(data, null, currentKey);
        tree.role = "tree";
        tree.setAttribute("aria-label", this.nav.getAttribute("aria-label"));
        tree.addEventListener("keydown", (event) => this.keydown(event));
        this.tree = tree;
        const current = this.items.find((item) => item.key === currentKey);
        for (let item = current; item; item = item.parent)
          this.expand(item, true);
        this.remember(current ?? this.items[0]);

        // Read focus at commit time: a delayed response must never steal it back.
        const focused = reserve.contains(document.activeElement)
          ? document.activeElement.dataset.treeKey
          : null;
        reserve.replaceWith(tree);
        if (focused) {
          const item = this.items.find(
            (candidate) => candidate.key === focused,
          );
          if (item) {
            for (let parent = item.parent; parent; parent = parent.parent)
              this.expand(parent, true);
            this.move(item);
          }
        }
      } catch {
        // Keep usable ordinary links after a failed request or invalid payload.
        this.items = [];
        this.last = null;
      } finally {
        status.hidden = true;
        indicator?.classList.remove("is-loading");
        this.nav.removeAttribute("aria-busy");
      }
    }

    build(nodes, parent, currentKey) {
      const list = document.createElement("ul");
      list.className = "c-nav c-nav--vertical unlist mte-tree__list";
      list.role = "group";
      for (const node of nodes) {
        if (
          typeof node.key !== "string" ||
          typeof node.label !== "string" ||
          !Array.isArray(node.children)
        )
          throw new Error("Invalid navigation");
        const li = document.createElement("li");
        li.role = "none";
        li.className = "c-nav__item c-nav__item--default";
        const depth = parent ? parent.depth + 1 : 0;
        li.style.setProperty("--mte-tree-depth", depth);
        const row = document.createElement("div");
        row.className = "c-nav__item-wrapper";
        const link = document.createElement(node.href ? "a" : "span");
        link.className = "c-nav__link";
        link.role = "treeitem";
        link.tabIndex = -1;
        link.textContent = node.label;
        link.id = `${this.nav.id}-item-${this.items.length}`;
        if (node.href) {
          const url = new URL(node.href, document.baseURI);
          if (!["http:", "https:", "mailto:", "tel:"].includes(url.protocol))
            throw new Error("Invalid link");
          link.href = url.href;
          link.target = node.target || "";
          link.rel =
            node.target === "_blank"
              ? `${node.rel || ""} noopener`
              : node.rel || "";
        }
        if (node.key === currentKey) {
          link.setAttribute("aria-current", "page");
          li.classList.add("is-current");
        }
        const item = {
          depth,
          key: node.key,
          label: node.label,
          link,
          parent,
          children: [],
          group: null,
          toggle: null,
        };
        this.items.push(item);
        parent?.children.push(item);
        link.addEventListener("focus", () => this.remember(item));
        link.addEventListener("click", () => {
          if (!node.href) this.expand(item);
        });
        row.append(link);
        li.append(row);
        if (node.children.length) {
          const toggle = document.createElement("button");
          toggle.type = "button";
          toggle.className = "mte-tree__toggle";
          toggle.tabIndex = -1;
          // The treeitem exposes expansion to assistive technology; this is its pointer hit target.
          toggle.setAttribute("aria-hidden", "true");
          item.toggle = toggle;
          item.group = this.build(node.children, item, currentKey);
          item.group.id = `${link.id}-children`;
          // aria-owns makes the sibling group a child of the link/treeitem.
          link.setAttribute("aria-owns", item.group.id);
          toggle.setAttribute("aria-controls", item.group.id);
          this.expand(item, false);
          toggle.addEventListener("click", () => {
            this.expand(item);
            this.move(item);
          });
          row.append(toggle);
          li.append(item.group);
        }
        list.append(li);
      }
      return list;
    }

    expand(item, open = item.link.getAttribute("aria-expanded") !== "true") {
      if (!item.group) return;
      item.link.setAttribute("aria-expanded", String(open));
      item.toggle.setAttribute("aria-expanded", String(open));
      item.toggle.setAttribute(
        "aria-label",
        `${open ? this.nav.dataset.treeCollapse : this.nav.dataset.treeExpand} ${item.label}`,
      );
      item.toggle.textContent = open ? "−" : "+";
      item.group.hidden = !open;
      item.link.closest("li").classList.toggle("is-open", open);
    }

    visible() {
      return this.items.filter((item) => !item.link.closest("[hidden]"));
    }

    remember(item) {
      if (this.last) this.last.tabIndex = -1;
      item.link.tabIndex = 0;
      this.last = item.link;
    }

    move(item) {
      if (!item) return;
      this.remember(item);
      item.link.focus();
    }

    keydown(event) {
      if (event.altKey || event.ctrlKey || event.metaKey || event.isComposing)
        return;
      const item = this.items.find(
        (candidate) => candidate.link === event.target,
      );
      if (!item) return;
      const visible = this.visible();
      const index = visible.indexOf(item);
      switch (event.key) {
        case "ArrowDown":
          this.move(visible[index + 1]);
          break;
        case "ArrowUp":
          this.move(visible[index - 1]);
          break;
        case "Home":
          this.move(visible[0]);
          break;
        case "End":
          this.move(visible.at(-1));
          break;
        case "ArrowRight":
          if (item.link.getAttribute("aria-expanded") === "true")
            this.move(item.children[0]);
          else this.expand(item, true);
          break;
        case "ArrowLeft":
          if (item.link.getAttribute("aria-expanded") === "true")
            this.expand(item, false);
          else this.move(item.parent);
          break;
        case " ":
          this.expand(item);
          break;
        case "Enter":
          if (item.link.hasAttribute("href")) return;
          this.expand(item);
          break;
        default: {
          if (event.key.length !== 1) return;
          const now = performance.now();
          this.search =
            now - this.searchTime > 700 ? event.key : this.search + event.key;
          this.searchTime = now;
          const query = [...this.search].every((letter) => letter === event.key)
            ? event.key
            : this.search;
          const ordered = [
            ...visible.slice(index + 1),
            ...visible.slice(0, index + 1),
          ];
          this.move(
            ordered.find((candidate) =>
              candidate.label
                .toLocaleLowerCase()
                .startsWith(query.toLocaleLowerCase()),
            ),
          );
        }
      }
      event.preventDefault();
    }
  }

  document.querySelectorAll("[data-tree-url]").forEach((nav) => {
    const instance = new Tree(nav);
    instances.set(nav, instance);
    if (nav.dataset.treeMobile === "true") return;
    const observer = new IntersectionObserver((entries) => {
      if (entries.some((entry) => entry.isIntersecting)) {
        observer.disconnect();
        instance.start();
      }
    });
    observer.observe(nav);
  });

  document.querySelectorAll("[data-tree-panel]").forEach((details) => {
    if (!("showModal" in HTMLDialogElement.prototype)) return;
    const summary = details.querySelector("summary");
    const content = details.querySelector("[data-tree-dialog-content]");
    const dialog = document.createElement("dialog");
    for (const attribute of content.attributes)
      dialog.setAttribute(attribute.name, attribute.value);
    dialog.append(...content.childNodes);
    content.remove();
    document.body.append(dialog);
    const opener = document.createElement("button");
    opener.type = "button";
    opener.className = `${summary.className} ${details.className}`;
    opener.append(...summary.childNodes);
    opener.setAttribute("aria-haspopup", "dialog");
    opener.setAttribute("aria-controls", dialog.id);
    details.replaceWith(opener);
    const close = dialog.querySelector("[data-tree-close]");
    close.hidden = false;
    const instance = instances.get(dialog.querySelector("[data-tree-url]"));
    opener.addEventListener("click", () => {
      dialog.showModal();
      instance.focus();
      instance.start();
    });
    close.addEventListener("click", () => dialog.close());
    dialog.addEventListener("close", () => opener.focus());
  });
})();
