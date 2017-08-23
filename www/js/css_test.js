/*
This script attempts to identify all CSS classes mentioned in HTML but not defined in the stylesheets.

In order to use it, just run it in the DevTools console (or add it to DevTools Snippets and run it from there).

Note that this script requires browser to support `fetch` and some ES6 features (fat arrow, Promises, Array.from, Set). You can transpile it to ES5 here: https://babeljs.io/repl/ .

Known limitations:
- it won't be able to take into account some external stylesheets (if CORS isn't set up) 
- it will produce false negatives for classes that are mentioned in the comments.
*/

(function () {
  "use strict";

  //get all unique CSS classes defined in the main document
  let allClasses = Array.from(document.querySelectorAll('*'))
    .map(n => Array.from(n.classList))
    .reduce((all, a) => all ? all.concat(a) : a)
    .reduce((all, i) => all.add(i), new Set());

  //load contents of all CSS stylesheets applied to the document
  let loadStyleSheets = Array.from(document.styleSheets)
    .map(s => {
      if (s.href) {
        return fetch(s.href)
          .then(r => r.text())
          .catch(e => {
            console.warn('Coudn\'t load ' + s.href + ' - skipping');
            return "";
          });
      }

      return s.ownerNode.innerText
    });

  Promise.all(loadStyleSheets).then(s => {
    let text = s.reduce((all, s) => all + s);

    //get a list of all CSS classes that are not mentioned in the stylesheets
    let undefinedClasses = Array.from(allClasses)
      .filter(c => {
        var rgx = new RegExp(escapeRegExp('.' + c) + '[^_a-zA-Z0-9-]');

        return !rgx.test(text);
      });

    if(undefinedClasses.length) {
        console.log('List of ' + undefinedClasses.length + ' undefined CSS classes: ', undefinedClasses);
    } else {
        console.log('All CSS classes are defined!');
    }
  });

  function escapeRegExp(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
  }

})();