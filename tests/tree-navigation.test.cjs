const { test } = require("node:test");
const assert = require("node:assert/strict");
const { readFileSync } = require("node:fs");
const { JSDOM } = require("jsdom");

test("native mobile dialog overrides Municipio drawer hiding in either stylesheet order", () => {
  // Municipio 6.43.3 keeps a right drawer off-screen until its own JS adds is-open.
  const upstream = `.c-drawer { visibility: hidden; opacity: 0; display: flex; transform: translate(-100%); }
    .c-drawer.c-drawer--right { transform: translate(100%); right: 0; left: initial; }`;
  const css = readFileSync(
    require.resolve("../assets/css/tree-navigation.css"),
    "utf8",
  );
  for (const styles of [upstream + css, css + upstream]) {
    const dom = new JSDOM(
      `<style>${styles}</style><dialog class="c-drawer c-drawer--right mte-mobile__content" data-tree-dialog-content open></dialog>`,
    );
    const dialog = dom.window.document.querySelector("dialog");
    const style = dom.window.getComputedStyle(dialog);
    assert.equal(style.transform, "none");
    assert.equal(style.display, "block");
    assert.equal(style.visibility, "visible");
    assert.equal(style.opacity, "1");
    dialog.removeAttribute("open");
    assert.equal(dom.window.getComputedStyle(dialog).display, "none");
    dom.window.close();
  }
});

const script = readFileSync(
  require.resolve("../assets/js/tree-navigation.js"),
  "utf8",
);
const nodes = [
  {
    key: "/0:1",
    id: "1",
    label: "Parent",
    href: "/parent",
    children: [
      {
        key: "/0:1/0:2",
        id: "2",
        label: "Child",
        href: "/child",
        children: [
          {
            key: "/0:1/0:2/0:3",
            id: "3",
            label: "Grandchild",
            href: "/grandchild",
            children: [],
          },
        ],
      },
    ],
  },
  { key: "/1:4", id: "4", label: "Other", href: "/other", children: [] },
];
const markup = (id = "nav") =>
  `<nav id="${id}" aria-label="Navigation" data-tree-url="/tree/v1" data-tree-nonce="" data-tree-mobile="false" data-tree-expand="Expand" data-tree-collapse="Collapse"><p role="status" hidden>Loading</p><div data-tree-reserve><a href="/parent" data-tree-key="/0:1">Parent</a><a href="/child" aria-current="page" data-tree-key="/0:1/0:2">Child</a></div></nav>`;
const tick = () => new Promise((resolve) => setImmediate(resolve));

function setup(
  html = markup(),
  fetcher = async () => ({ ok: true, json: async () => nodes }),
  beforeScript = () => {},
) {
  const dom = new JSDOM(html, {
    url: "https://example.test/",
    runScripts: "outside-only",
  });
  let calls = 0;
  const observers = [];
  dom.window.fetch = (...args) => {
    calls++;
    return fetcher(...args);
  };
  dom.window.AbortSignal.timeout = () =>
    new dom.window.AbortController().signal;
  dom.window.IntersectionObserver = class {
    constructor(callback) {
      this.callback = callback;
      observers.push(this);
    }
    observe() {}
    disconnect() {}
  };
  beforeScript(dom.window);
  dom.window.eval(script);
  return {
    dom,
    document: dom.window.document,
    calls: () => calls,
    start: () =>
      observers.forEach((observer) =>
        observer.callback([{ isIntersecting: true }]),
      ),
  };
}

function key(dom, value) {
  dom.window.document.activeElement.dispatchEvent(
    new dom.window.KeyboardEvent("keydown", {
      key: value,
      bubbles: true,
      cancelable: true,
    }),
  );
}

test("lazy initialization shares one fetch and keeps independent IDs and expansion", async () => {
  const env = setup(markup("one") + markup("two"));
  assert.equal(env.calls(), 0);
  env.start();
  await tick();
  assert.equal(env.calls(), 1);
  assert.equal(env.document.querySelectorAll("[role=tree]").length, 2);
  const ids = [...env.document.querySelectorAll("[id]")].map((node) => node.id);
  assert.equal(new Set(ids).size, ids.length);
  const one = env.document.querySelector("#one [aria-current=page]");
  assert.equal(one.getAttribute("aria-expanded"), "true");
  assert.equal(
    env.document
      .querySelector('#one a[href="https://example.test/grandchild"]')
      .closest("[hidden]"),
    null,
  );
  one.focus();
  key(env.dom, "ArrowLeft");
  assert.equal(one.getAttribute("aria-expanded"), "false");
  assert.equal(
    env.document
      .querySelector("#two [aria-current=page]")
      .getAttribute("aria-expanded"),
    "true",
  );
  assert.equal(
    env.document.querySelectorAll('[role=treeitem][tabindex="0"]').length,
    2,
  );
});

test("arrows, Home/End, typeahead and Space follow visible nodes and retain nested expansion", async () => {
  const env = setup();
  env.start();
  await tick();
  const current = env.document.querySelector("[aria-current=page]");
  current.focus();
  key(env.dom, "ArrowRight");
  key(env.dom, "ArrowRight");
  assert.equal(env.document.activeElement.textContent, "Grandchild");
  key(env.dom, "ArrowLeft");
  key(env.dom, "ArrowUp");
  assert.equal(env.document.activeElement.textContent, "Parent");
  key(env.dom, " ");
  key(env.dom, " ");
  assert.equal(current.getAttribute("aria-expanded"), "true");
  key(env.dom, "End");
  assert.equal(env.document.activeElement.textContent, "Other");
  key(env.dom, " ");
  assert.equal(env.document.activeElement.hasAttribute("aria-expanded"), false);
  key(env.dom, "Home");
  key(env.dom, "c");
  assert.equal(env.document.activeElement, current);
});

test("failed fetch retains working ordinary links and clears loading state", async () => {
  const env = setup(markup(), async () => {
    throw new Error("offline");
  });
  const current = env.document.querySelector("[aria-current=page]");
  current.focus();
  env.start();
  await tick();
  assert.equal(env.document.activeElement, current);
  assert.equal(
    env.document.querySelectorAll("[data-tree-reserve] a").length,
    2,
  );
  assert.equal(env.document.querySelector("[role=status]").hidden, true);
  assert.equal(
    env.document.querySelector("nav").hasAttribute("aria-busy"),
    false,
  );
});

test("late response transfers only focus still inside the reserve", async () => {
  let complete;
  const env = setup(
    markup() + '<button id="outside">Outside</button>',
    () =>
      new Promise((resolve) => {
        complete = resolve;
      }),
  );
  env.document.querySelector("[aria-current=page]").focus();
  env.start();
  env.document.querySelector("#outside").focus();
  complete({ ok: true, json: async () => nodes });
  await tick();
  assert.equal(env.document.activeElement.id, "outside");

  const second = setup();
  second.document.querySelector("[aria-current=page]").focus();
  second.start();
  await tick();
  assert.equal(second.document.activeElement.getAttribute("role"), "treeitem");
  assert.equal(second.document.activeElement.textContent, "Child");
});

test("malformed or unsafe data preserves the reserve without inserting executable HTML", async () => {
  const env = setup(markup(), async () => ({
    ok: true,
    json: async () => [{ ...nodes[0], href: "javascript:alert(1)" }],
  }));
  env.start();
  await tick();
  assert.ok(env.document.querySelector("[data-tree-reserve]"));
  assert.equal(env.document.querySelector("[role=tree]"), null);
});

test("mobile open is lazy, restores the last node and returns focus on close", async () => {
  const html = `<details data-tree-panel><summary class="trigger">Menu</summary><div id="panel" data-tree-dialog-content aria-label="Menu"><button data-tree-close hidden>Close</button>${markup().replace('data-tree-mobile="false"', 'data-tree-mobile="true"')}</div></details>`;
  const env = setup(html, undefined, (window) => {
    delete window.HTMLDialogElement.prototype.closedBy;
    window.HTMLDialogElement.prototype.showModal = function () {
      this.open = true;
    };
    window.HTMLDialogElement.prototype.close = function () {
      this.open = false;
      this.dispatchEvent(new window.Event("close"));
    };
  });
  assert.equal(env.calls(), 0);
  const opener = env.document.querySelector("[aria-haspopup=dialog]");
  opener.click();
  assert.equal(env.document.activeElement.textContent, "Child");
  await tick();
  assert.equal(env.calls(), 1);
  const dialog = env.document.querySelector("dialog");
  assert.equal(dialog.getAttribute("closedby"), "any");
  dialog.getBoundingClientRect = () => ({
    left: 400,
    right: 720,
    top: 0,
    bottom: 800,
  });
  const pointer = (type, x) =>
    dialog.dispatchEvent(
      new env.dom.window.MouseEvent(type, {
        bubbles: true,
        button: 0,
        clientX: x,
        clientY: 100,
      }),
    );
  pointer("pointerdown", 500);
  pointer("click", 500);
  assert.equal(dialog.open, true, "Panel whitespace must not dismiss");
  pointer("pointerdown", 500);
  pointer("click", 200);
  assert.equal(dialog.open, true, "Dragging out of the panel must not dismiss");
  pointer("pointerdown", 200);
  pointer("click", 200);
  assert.equal(dialog.open, false);
  assert.equal(env.document.activeElement, opener);
  opener.click();
  key(env.dom, "End");
  env.document.querySelector("[data-tree-close]").click();
  assert.equal(env.document.activeElement, opener);
  opener.click();
  assert.equal(env.document.activeElement.textContent, "Other");
  assert.equal(env.calls(), 1);
});

test("mobile dismissal waits for animation and handles closing during entry", () => {
  for (const trigger of ["button", "cancel", "backdrop"]) {
    const animations = [];
    const env = setup(
      `<details data-tree-panel><summary>Menu</summary><div id="panel" data-tree-dialog-content><button data-tree-close>Close</button>${markup().replace('data-tree-mobile="false"', 'data-tree-mobile="true"')}</div></details>`,
      undefined,
      (window) => {
        delete window.HTMLDialogElement.prototype.closedBy;
        window.matchMedia = () => ({ matches: false });
        window.HTMLDialogElement.prototype.showModal = function () {
          this.open = true;
        };
        window.HTMLDialogElement.prototype.close = function () {
          this.open = false;
          this.dispatchEvent(new window.Event("close"));
        };
        window.Element.prototype.animate = function (frames, options) {
          const animation = {
            frames,
            options,
            cancelled: false,
            cancel() {
              this.cancelled = true;
            },
          };
          animations.push(animation);
          return animation;
        };
      },
    );
    const opener = env.document.querySelector("[aria-haspopup=dialog]");
    const dialog = env.document.querySelector("dialog");
    opener.click();
    assert.equal(animations[0].options.duration, 300);
    assert.equal(animations[0].options.easing, "ease-out");
    if (trigger === "button")
      env.document.querySelector("[data-tree-close]").click();
    if (trigger === "cancel") {
      const event = new env.dom.window.Event("cancel", { cancelable: true });
      dialog.dispatchEvent(event);
      assert.equal(event.defaultPrevented, true);
    }
    if (trigger === "backdrop") {
      for (const type of ["pointerdown", "click"])
        dialog.dispatchEvent(
          new env.dom.window.MouseEvent(type, { clientX: -1, button: 0 }),
        );
    }
    assert.equal(animations[0].cancelled, true);
    assert.equal(dialog.open, true, "Dialog stays modal during exit");
    assert.equal(animations[1].options.easing, "ease-in");
    env.document.querySelector("[data-tree-close]").click();
    assert.equal(
      animations.length,
      2,
      "Repeated dismissal must not restart exit",
    );
    animations[1].onfinish();
    assert.equal(dialog.open, false);
    assert.equal(env.document.activeElement, opener);
    assert.equal(dialog.hasAttribute("data-tree-closing"), false);
    opener.click();
    assert.equal(dialog.open, true);
    assert.equal(animations.length, 3);
    env.dom.window.close();
  }
});

test("reduced motion opens and closes immediately without animation", () => {
  const env = setup(
    `<details data-tree-panel><summary>Menu</summary><div id="panel" data-tree-dialog-content><button data-tree-close>Close</button>${markup().replace('data-tree-mobile="false"', 'data-tree-mobile="true"')}</div></details>`,
    undefined,
    (window) => {
      window.matchMedia = () => ({ matches: true });
      window.Element.prototype.animate = () =>
        assert.fail("Reduced motion must not animate");
      window.HTMLDialogElement.prototype.showModal = function () {
        this.open = true;
      };
      window.HTMLDialogElement.prototype.close = function () {
        this.open = false;
        this.dispatchEvent(new window.Event("close"));
      };
    },
  );
  const opener = env.document.querySelector("[aria-haspopup=dialog]");
  opener.click();
  env.document.querySelector("[data-tree-close]").click();
  assert.equal(env.document.querySelector("dialog").open, false);
  assert.equal(env.document.activeElement, opener);
  env.dom.window.close();
});
