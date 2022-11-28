/* --------------------------------------------------
 * Accela
 */

(async function(){
  const utils = {
    str2DOM: (str, wrapTagName="div") => {
      const dom = document.createElement(wrapTagName);
      dom.innerHTML = str;
      return dom;
    },

    bindProps: (content, props) => {
      if(typeof props === "undefined") throw new Error("props is undefined");

      content.querySelectorAll("[data-bind]").forEach(o => {
        o.getAttribute("data-bind").split(",").forEach(function(set){
          const [prop, variable] = set.split(":");
          if(!variable) variable = prop;
          o.setAttribute(prop, props[variable]);
        });
      });

      content.querySelectorAll("[data-bind-html]").forEach(o => {
        o.innerHTML = props[o.getAttribute("data-bind-html")];
      });

      content.querySelectorAll("[data-bind-text]").forEach(o => {
        o.innerText = props[o.getAttribute("data-bind-text")];
      });
    },

    applyComponents: (content, components, _props={}, depth=1) => {
      if(depth > 1000) throw new Error("error!");

      if(content.tagName === "COMPONENT"){
        const props = {};

        content.getAttributeNames().forEach(propName => {
          const prop = content.getAttribute(propName);
          if(propName[0] === "@" && _props[prop]){
            props[propName.slice[1]] = _props[propName];
          }else{
            props[propName] = content.getAttribute(propName);
          }
        });

        const componentName = content.getAttribute("use");
        const component = components[componentName];
        if(!component) throw new Error(`component ${componentName} is not exists`);

        const componentObject = component.object.cloneNode(true);
        utils.bindProps(componentObject, props);

        componentObject.querySelectorAll(`[data-contents="${componentName}"]`).forEach(_o => {
          _o.innerHTML = content.innerHTML;
        });
        utils.applyComponents(componentObject, components, props, depth+1);
        content.parentNode.replaceChild(componentObject.firstElementChild, content);

      }else{
        content.querySelectorAll(":scope > *").forEach(_o => {
          utils.applyComponents(_o, components, _props, depth+1);
        });
      }

      return this;
    },

    applyModules: (contents, depth=1) => {
      if(depth > 1000) throw new Error("error!");

      contents.querySelectorAll(":scope > *").forEach(o => {
        utils.applyModules(o, depth+1);
      });

      const moduleName = contents.getAttribute("data-use-module");
      if(moduleName !== null && ACCELA.modules[moduleName]){
        ACCELA.modules[moduleName](contents);
      }
    },
  };

  class Page {
    constructor(page){
      this.path = page.path;
      this.head = new PageHead(page.path, page.head, page.props);
      this.content = new PageContent(page.path, page.content, page.props);
    }
  }

  class PageHead {
    constructor(path, head, props){
      this.object = (o => {
        o.innerHTML = head;
        return o;
      })(document.createElement("accela:head"));

      utils.bindProps(this.object, props);
    }

    html(){
      return this.object.innerHTML;
    }
  }

  class PageContent {
    constructor(path, content, props){
      this.object = (o => {
        o.innerHTML = content;
        return o;
      })(document.createElement("accela:content"));

      this.path = path;
      this.props = props;
      utils.bindProps(this.object, props);
    }

    html(){
      const contents = this.object.cloneNode(true);
      utils.applyModules(contents);
      return contents;
    }

    applyComponents(components){
      utils.applyComponents(this.object, components, this.props);
    }
  }

  class Component {
    constructor(name, component){
      this.object = (o => {
        o.setAttribute("data-name", name);
        o.innerHTML = component;
        o.querySelector("[data-contents]").setAttribute("data-contents", name);

        return o;
      })(document.createElement("accela:component"));
    }
  }

  const head = document.querySelector("head"),
        body = document.getElementById("accela");

  const components = {};
  Object.entries(ACCELA.components).forEach(([name, component]) => {
    components[name] = new Component(name, component);
  });

  const movePage = (page, isFirst) => {
    if(!ACCELA.changePageContent){
      ACCELA.changePageContent = (body, pageContent) => {
        body.innerHTML = pageContent;
      };
    }

    const pageContent = page.content.html();

    const move = () => {
      document.querySelectorAll("html, body").forEach(o => {
        o.scrollTop = 0;
      })

      const tags = head.querySelectorAll("*");
      (() => {
        let isDynamicTags = false;

        tags.forEach(o => {
          if(o.getAttribute("name") === "accela-separator") isDynamicTags = true;
          if(!isDynamicTags) return;

          if(o.classList.contains("accela-css")) return;
          o.remove();
        });
      })();

      ((div) => {
        div.innerHTML = page.head.html();
        div.querySelectorAll(":scope > *").forEach(o => {
          head.appendChild(o);
        });
      })(document.createElement("div"));
      ACCELA.changePageContent(body, pageContent.querySelector(":scope > *"));
      body.setAttribute("data-page-path", page.path);
    }

    if(isFirst){
      if(ACCELA.initPage) ACCELA.initPage();
      move();
    }else{
      ACCELA.movePage ? ACCELA.movePage(pageContent, move) : move();
    }
  };

  const firstPage = new Page(ACCELA.entrance_page);
  firstPage.content.applyComponents(components);

  movePage(firstPage, true);


  const res = await fetch(`/assets/site.json?__t=${ACCELA.utime}`);
  const site = {};

  Object.entries(await res.json()).forEach(([path, _page]) => {
    const page = new Page(_page);
    page.content.applyComponents(components);
    site[path] = page;
  });

  document.querySelector("body").addEventListener("click", e => {
    const target = e.target;
    if(target.tagName !== "A") return true;
    if(e.metaKey || e.shiftKey || e.altKey) return true;

    const url = new URL(target.getAttribute("href"), location.href);
    const path = url.pathname;
    if(!site[path]) return true;

    e.preventDefault();
    if(path === location.pathname) return false;

    movePage(site[path]);
    history.pushState(null, null, path);

    return false;
  });

  window.onpopstate = (e) => {
    if(e.originalEvent && !e.originalEvent.state) return;
    movePage(site[location.pathname]);
  };
})();
