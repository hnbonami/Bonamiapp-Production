// Source: https://github.com/focus-trap/focus-trap
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = global || self, global.focusTrap = factory());
}(this, (function () { 'use strict';

  function ownKeys(object, enumerableOnly) {
    var keys = Object.keys(object);

    if (Object.getOwnPropertySymbols) {
      var symbols = Object.getOwnPropertySymbols(object);

      if (enumerableOnly) {
        symbols = symbols.filter(function (sym) {
          return Object.getOwnPropertyDescriptor(object, sym).enumerable;
        });
      }

      keys.push.apply(keys, symbols);
    }

    return keys;
  }

  function _objectSpread2(target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i] != null ? arguments[i] : {};

      if (i % 2) {
        ownKeys(Object(source), true).forEach(function (key) {
          _defineProperty(target, key, source[key]);
        });
      } else if (Object.getOwnPropertyDescriptors) {
        Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
      } else {
        ownKeys(Object(source)).forEach(function (key) {
          Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
        });
      }
    }

    return target;
  }

  function _defineProperty(obj, key, value) {
    if (key in obj) {
      Object.defineProperty(obj, key, {
        value: value,
        enumerable: true,
        configurable: true,
        writable: true
      });
    } else {
      obj[key] = value;
    }

    return obj;
  }

  var candidateSelectors = ['input', 'select', 'textarea', 'a[href]', 'button', '[tabindex]', 'audio[controls]', 'video[controls]', '[contenteditable]:not([contenteditable="false"])', 'details>summary:first-of-type', 'details'];
  var candidateSelector = /* #__PURE__ */candidateSelectors.join(',');

  var getCandidates = function getCandidates(el, includeContainer, filter) {
    var candidates = Array.prototype.slice.apply(el.querySelectorAll(candidateSelector));

    if (includeContainer && el.matches(candidateSelector)) {
      candidates.unshift(el);
    }

    if (filter) {
      candidates = candidates.filter(filter);
    }

    return candidates;
  };

  var getTabindex = function getTabindex(node) {
    var tabindexAttr = parseInt(node.getAttribute('tabindex'), 10);

    if (!isNaN(tabindexAttr)) {
      return tabindexAttr;
    } // Browsers do not return `tabIndex` correctly for contentEditable nodes;
    // so if they don't have a tabindex attribute specified, we simulate the
    // behavior by returning 0 for items with contentEditable=true, and -1 for
    // contentEditable=false.
    // See https://github.com/focus-trap/focus-trap-react/issues/118


    if (node.contentEditable === 'true') {
      return 0;
    } // in Chrome, <details/>, <audio controls/> and <video controls/> elements get a default
    // `tabIndex` of 0, but we can't assume that all browsers do the same


    if (node.nodeName === 'AUDIO' || node.nodeName === 'VIDEO' || node.nodeName === 'DETAILS') {
      return 0;
    }

    return node.tabIndex;
  };

  var sortOrderedTabbables = function sortOrderedTabbables(a, b) {
    return a.tabIndex === b.tabIndex ? a.documentOrder - b.documentOrder : a.tabIndex - b.tabIndex;
  };

  var getTabbableCandidates = function getTabbableCandidates(el, includeContainer, filter) {
    var candidates = getCandidates(el, includeContainer, filter);
    var regularTabbables = [];
    var orderedTabbables = [];
    candidates.forEach(function (candidate, i) {
      var tabIndex = getTabindex(candidate);

      if (tabIndex === 0) {
        regularTabbables.push(candidate);
      } else if (tabIndex > 0) {
        orderedTabbables.push({
          documentOrder: i,
          tabIndex: tabIndex,
          node: candidate
        });
      }
    });
    var sortedTabbables = orderedTabbables.sort(sortOrderedTabbables).map(function (a) {
      return a.node;
    });
    return Array.prototype.slice.apply(sortedTabbables).concat(Array.prototype.slice.apply(regularTabbables));
  };

  var getTabbableNodes = function getTabbableNodes(el, includeContainer) {
    return getTabbableCandidates(el, includeContainer, function (node) {
      return getTabindex(node) >= 0;
    });
  };

  var isSelectableInput = function isSelectableInput(node) {
    return node.tagName && node.tagName.toLowerCase() === 'input' && typeof node.select === 'function';
  };

  var isHidden = function isHidden(node) {
    // offsetParent being null will allow detecting cases where an element is invisible or inside an invisible element,
    // as long as the element does not have position: fixed. For them, their visibility has to be checked directly.
    // https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/offsetParent
    if (getComputedStyle(node).visibility === 'hidden') {
      return true;
    }

    var isDirectSummary = node.matches('details>summary:first-of-type');
    var nodeUnderDetails = isDirectSummary ? node.parentElement : node;

    if (nodeUnderDetails.matches('details:not([open]) *')) {
      return true;
    } // This is the simplest way to detect a hidden element, but it's not foolproof.
    // For example, if an element has 0 size and no borders, it's still considered visible.
    // This is valid in the sense that the element is still in the layout and still might be focusable.
    // It's also what browsers do, and it's what vanilla-focus-trap is doing, so we're being consistent.
    //
    // A more robust way would be to check for element intersection with the viewport using Intersection Observer,
    // but that's a lot more complicated and this should be good enough for the vast majority of cases.


    return node.offsetParent === null;
  };

  var isNodeMatchingSelectorFocusable = function isNodeMatchingSelectorFocusable(options, node) {
    if (node.disabled || node.hasAttribute('disabled') || // For a details element, the summary is the only focusable part.
    node.matches('details') && Array.prototype.slice.apply(node.children).some(function (child) {
      return child.tagName === 'SUMMARY';
    })) {
      return false;
    }

    if (options.displayCheck === 'full' && isHidden(node)) {
      return false;
    }

    return true;
  };

  var getFocusableCandidates = function getFocusableCandidates(el, includeContainer, options) {
    var candidates = getCandidates(el, includeContainer);

    if (options.tabbable) {
      candidates = getTabbableCandidates(el, includeContainer);
    } // focusable candidates are tabbable candidates that are not disabled


    return candidates.filter(function (candidate) {
      return isNodeMatchingSelectorFocusable(options, candidate);
    });
  };

  var getFocusableNodes = function getFocusableNodes(el, includeContainer) {
    var candidates = getTabbableNodes(el, includeContainer); // focusable nodes are tabbable nodes that are not disabled and are visible

    return candidates.filter(function (candidate) {
      return isNodeMatchingSelectorFocusable({
        displayCheck: 'full'
      }, candidate);
    });
  };

  var activeFocusTraps = function () {
    var trapQueue = [];
    return {
      activateTrap: function activateTrap(trap) {
        if (trapQueue.length > 0) {
          var activeTrap = trapQueue[trapQueue.length - 1];

          if (activeTrap !== trap) {
            activeTrap.pause();
          }
        }

        var trapIndex = trapQueue.indexOf(trap);

        if (trapIndex === -1) {
          trapQueue.push(trap);
        } else {
          // move this existing trap to the front of the queue
          trapQueue.splice(trapIndex, 1);
          trapQueue.push(trap);
        }
      },
      deactivateTrap: function deactivateTrap(trap) {
        var trapIndex = trapQueue.indexOf(trap);

        if (trapIndex !== -1) {
          trapQueue.splice(trapIndex, 1);
        }

        if (trapQueue.length > 0) {
          trapQueue[trapQueue.length - 1].unpause();
        }
      }
    };
  }();

  var isescapekey = function isescapekey(e) {
    return e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27;
  };

  var iskey = function iskey(e, name) {
    return e.key === name || e.keyCode === e.which;
  };

  var istabkey = function istabkey(e) {
    return e.key === 'Tab' || e.keyCode === 9;
  };

  var delay = function delay(fn) {
    return setTimeout(fn, 0);
  };

  var findIndex = function findIndex(arr, fn) {
    var idx = -1;
    arr.every(function (val, i) {
      if (fn(val)) {
        idx = i;
        return false; // break
      }

      return true; // continue
    });
    return idx;
  };

  var valueOrHandler = function valueOrHandler(value) {
    for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      params[_key - 1] = arguments[_key];
    }

    return typeof value === 'function' ? value.apply(void 0, params) : value;
  };

  var createFocusTrap = function createFocusTrap(elements, userOptions) {
    //
    // private state
    //
    var doc = document;
    var container;
    var config = _objectSpread2({
      returnFocusOnDeactivate: true,
      escapeDeactivates: true,
      delayInitialFocus: true
    }, userOptions);
    var state = {
      // containers given to createFocusTrap()
      // @type {Array<HTMLElement>}
      containers: [],
      // list of objects identifying tabbable nodes in `containers` in the trap
      // NOTE: it's possible that a group has no tabbable nodes if nodes get removed while the trap
      //  is active, but the trap should never get to a state where there isn't at least one group
      //  with at least one tabbable node in it
      // @type {Array<{
      //   container: HTMLElement,
      //   tabbableNodes: Array<HTMLElement>, // empty if none
      //   focusableNodes: Array<HTMLElement>, // empty if none
      //   firstTabbableNode: HTMLElement|undefined,
      //   lastTabbableNode: HTMLElement|undefined,
      //   nextTabbableNode: (node: HTMLElement) => HTMLElement|undefined
      // }>}
      nodeGroups: [],
      // references to objects in `nodeGroups`
      // @type {HTMLElement|undefined}
      firstTabbableNode: undefined,
      // @type {HTMLElement|undefined}
      lastTabbableNode: undefined,
      // the node that had focus before the trap was activated
      // @type {HTMLElement|undefined}
      nodeToRestore: undefined,
      // the node that is currently focused
      // @type {HTMLElement|undefined}
      activeElement: undefined,
      // whether the trap is currently active
      // @type {boolean}
      active: false,
      // whether the trap is currently paused
      // @type {boolean}
      paused: false
    }; //
    // public API
    //

    var trap = {
      activate: activate,
      deactivate: deactivate,
      pause: pause,
      unpause: unpause
    }; //
    // setup
    //

    var initialContainers = Array.isArray(elements) ? elements : [elements];
    state.containers = initialContainers.map(function (el) {
      return typeof el === 'string' ? doc.querySelector(el) : el;
    }).filter(Boolean);
    container = state.containers[0]; //
    // private functions
    //

    var getOption = function getOption(configOverrideOptions, optionName, fallbackValue) {
      var optionValue = configOverrideOptions && configOverrideOptions[optionName] !== undefined ? configOverrideOptions[optionName] : config[optionName];
      var value = optionValue !== undefined ? optionValue : fallbackValue;
      return valueOrHandler(value);
    };

    var findNodeGroup = function findNodeGroup(node) {
      // find the group that contains this node
      var aGroup = state.nodeGroups.find(function (_ref) {
        var container = _ref.container,
            tabbableNodes = _ref.tabbableNodes;
        return container.contains(node) || // if the node is a container, it's not contained by itself, so we need to check
        // whether it's one of the containers in the group
        tabbableNodes.find(function (n) {
          return n === node;
        });
      }); // if the node is not in any group, then it's not in the trap, so we can't find a group
      // (this happens if the given node is not a tabbable node in the trap)

      return aGroup;
    };

    var getNextTabbableNode = function getNextTabbableNode(node) {
      var aGroup = findNodeGroup(node);

      if (!aGroup) {
        return undefined;
      } // find the node's index in the group's tabbable nodes


      var nodeIndex = findIndex(aGroup.tabbableNodes, function (n) {
        return n === node;
      });

      if (nodeIndex < 0) {
        // this can happen if the given node is a focusable node, but not a tabbable node
        return undefined;
      }

      var nextNode = aGroup.tabbableNodes[nodeIndex + 1];

      if (nextNode) {
        return nextNode;
      } // the node is the last tabbable node in the group, so find the next group


      var groupIndex = findIndex(state.nodeGroups, function (g) {
        return g === aGroup;
      });

      if (groupIndex < 0) {
        // this should not happen
        return undefined;
      }

      var nextGroup = state.nodeGroups[groupIndex + 1]; // if there is a next group, return the first tabbable node in it

      if (nextGroup) {
        return nextGroup.firstTabbableNode;
      } // there is no next group, so loop back to the first tabbable node of the first group


      return state.firstTabbableNode;
    };

    var getPrevTabbableNode = function getPrevTabbableNode(node) {
      var aGroup = findNodeGroup(node);

      if (!aGroup) {
        return undefined;
      } // find the node's index in the group's tabbable nodes


      var nodeIndex = findIndex(aGroup.tabbableNodes, function (n) {
        return n === node;
      });

      if (nodeIndex < 0) {
        return undefined;
      }

      var prevNode = aGroup.tabbableNodes[nodeIndex - 1];

      if (prevNode) {
        return prevNode;
      } // the node is the first tabbable node in the group, so find the previous group


      var groupIndex = findIndex(state.nodeGroups, function (g) {
        return g === aGroup;
      });

      if (groupIndex < 0) {
        // this should not happen
        return undefined;
      }

      var prevGroup = state.nodeGroups[groupIndex - 1]; // if there is a previous group, return the last tabbable node in it

      if (prevGroup) {
        return prevGroup.lastTabbableNode;
      } // there is no previous group, so loop back to the last tabbable node of the last group


      return state.lastTabbableNode;
    };

    var updateTabbableNodes = function updateTabbableNodes() {
      state.nodeGroups = state.containers.map(function (container) {
        var tabbableNodes = getTabbableNodes(container);
        var focusableNodes = getFocusableNodes(container);
        return {
          container: container,
          tabbableNodes: tabbableNodes,
          focusableNodes: focusableNodes,
          firstTabbableNode: tabbableNodes.length > 0 ? tabbableNodes[0] : undefined,
          lastTabbableNode: tabbableNodes.length > 0 ? tabbableNodes[tabbableNodes.length - 1] : undefined,
          nextTabbableNode: getNextTabbableNode,
          prevTabbableNode: getPrevTabbableNode
        };
      });
      state.firstTabbableNode = state.nodeGroups[0] && state.nodeGroups[0].firstTabbableNode;
      state.lastTabbableNode = state.nodeGroups[state.nodeGroups.length - 1] && state.nodeGroups[state.nodeGroups.length - 1].lastTabbableNode;
    };

    var checkPointerDown = function checkPointerDown(e) {
      if (getOption({}, 'clickOutsideDeactivates') && !isAnyTabbableNode(e.target)) {
        deactivate({
          returnFocus: false
        });
      }
    };

    var checkFocusIn = function checkFocusIn(e) {
      var target = e.target; // in some cases, the focus target is the document's `body` element;
      // in those cases, `activeElement` will be the `body` element, and we're fine
      // with that, so we should ignore the focus event that's being dispatched on the `body`
      // itself, and not try to contain it;
      // this happens, for example, when the browser window is blurred, and then the user
      // clicks on the document's `body` element, which has `tabIndex: -1` (or no `tabIndex`
      // at all, which is the same as -1)

      if (target === doc.body) {
        return;
      } // if the target is not in any of the containers, we need to set focus back to the
      // last known `activeElement`


      if (isAnyTabbableNode(target)) {
        // target is in the trap, so all good
        state.activeElement = target;
        return;
      } // target is not in the trap, so focus must be returned to the trap


      e.preventDefault();
      (state.activeElement || state.firstTabbableNode).focus();
    };

    var checkKey = function checkKey(e) {
      if (isescapekey(e) && getOption({}, 'escapeDeactivates') !== false) {
        e.preventDefault();
        deactivate();
        return;
      }

      if (istabkey(e)) {
        checkTab(e);
        return;
      }
    };

    var checkTab = function checkTab(e) {
      var target = e.target;
      updateTabbableNodes();
      var shiftKey = e.shiftKey;
      var isFirstTabbableNode = target === state.firstTabbableNode;
      var isLastTabbableNode = target === state.lastTabbableNode;

      if (shiftKey && isFirstTabbableNode) {
        // YES shift + tab on first tabbable node in trap
        e.preventDefault();
        var lastTabbableNode = state.lastTabbableNode;

        if (lastTabbableNode) {
          lastTabbableNode.focus();
        }

        return;
      }

      if (!shiftKey && isLastTabbableNode) {
        // YES tab on last tabbable node in trap
        e.preventDefault();
        var firstTabbableNode = state.firstTabbableNode;

        if (firstTabbableNode) {
          firstTabbableNode.focus();
        }

        return;
      } // NOT tab on first or last tabbable node in trap
      // (so we must be somewhere in the middle of the trap)
      //
      // find the node's group


      var aGroup = findNodeGroup(target);

      if (!aGroup) {
        // this should not happen
        return;
      }

      var nodeIndex = findIndex(aGroup.tabbableNodes, function (n) {
        return n === target;
      });

      if (nodeIndex < 0) {
        // this should not happen
        return;
      }

      if (shiftKey) {
        // shift + tab
        var prevNode = aGroup.tabbableNodes[nodeIndex - 1];

        if (!prevNode) {
          // it's the first node in the group, so we need to go to the last node of the previous group
          var groupIndex = findIndex(state.nodeGroups, function (g) {
            return g === aGroup;
          });

          if (groupIndex > 0) {
            var prevGroup = state.nodeGroups[groupIndex - 1];
            var lastNode = prevGroup.lastTabbableNode;

            if (lastNode) {
              e.preventDefault();
              lastNode.focus();
            }
          }
        }
      } else {
        // tab
        var nextNode = aGroup.tabbableNodes[nodeIndex + 1];

        if (!nextNode) {
          // it's the last node in the group, so we need to go to the first node of the next group
          var _groupIndex = findIndex(state.nodeGroups, function (g) {
            return g === aGroup;
          });

          if (_groupIndex < state.nodeGroups.length - 1) {
            var nextGroup = state.nodeGroups[_groupIndex + 1];
            var firstNode = nextGroup.firstTabbableNode;

            if (firstNode) {
              e.preventDefault();
              firstNode.focus();
            }
          }
        }
      }
    };

    var isAnyTabbableNode = function isAnyTabbableNode(node) {
      return state.nodeGroups.some(function (_ref2) {
        var tabbableNodes = _ref2.tabbableNodes;
        return tabbableNodes.some(function (n) {
          return n === node;
        });
      });
    };

    var tryFocus = function tryFocus(node) {
      if (node === doc.activeElement) {
        return;
      }

      if (!node || !node.focus) {
        tryFocus(state.firstTabbableNode);
        return;
      }

      node.focus({
        preventScroll: getOption({}, 'preventScroll')
      });
      state.activeElement = node;

      if (isSelectableInput(node)) {
        node.select();
      }
    };

    function activate(activateOptions) {
      if (state.active) {
        return;
      }

      updateTabbableNodes(); // check for an empty trap

      var hasTabbableNodes = state.nodeGroups.some(function (g) {
        return g.tabbableNodes.length > 0;
      });

      if (!hasTabbableNodes) {
        console.error('Focus-trap: There are no tabbable nodes in the trap. This is a problem. If you are trying to trap focus on an element that has no tabbable nodes, you should add `tabindex="0"` to the element that you want to be focusable.');
        return;
      }

      state.active = true;
      state.paused = false;
      state.nodeToRestore = doc.activeElement;
      var onActivate = getOption(activateOptions, 'onActivate');

      if (onActivate) {
        onActivate();
      }

      addListeners();
      var initialFocus = getOption(activateOptions, 'initialFocus');

      if (initialFocus !== false) {
        var initialFocusNode;

        if (initialFocus !== undefined) {
          // user specified initialFocus node
          initialFocusNode = typeof initialFocus === 'string' ? doc.querySelector(initialFocus) : initialFocus;
        } else {
          // no user specified initialFocus node, so we'll take the first tabbable node
          initialFocusNode = state.firstTabbableNode;
        }

        if (getOption(activateOptions, 'delayInitialFocus')) {
          delay(function () {
            return tryFocus(initialFocusNode);
          });
        } else {
          tryFocus(initialFocusNode);
        }
      }
    }

    function deactivate(deactivateOptions) {
      if (!state.active) {
        return;
      }

      removeListeners();
      state.active = false;
      state.paused = false;
      activeFocusTraps.deactivateTrap(trap);
      var onDeactivate = getOption(deactivateOptions, 'onDeactivate');

      if (onDeactivate) {
        onDeactivate();
      }

      var returnFocus = getOption(deactivateOptions, 'returnFocus');

      if (returnFocus) {
        delay(function () {
          tryFocus(state.nodeToRestore);
        });
      }
    }

    function pause() {
      if (state.paused || !state.active) {
        return;
      }

      state.paused = true;
      removeListeners();
    }

    function unpause() {
      if (!state.paused || !state.active) {
        return;
      }

      state.paused = false;
      addListeners();
    }

    function addListeners() {
      if (!state.active) {
        return;
      } // trap is being activated


      activeFocusTraps.activateTrap(trap); // if there is a click outside handler, also add a pointer down listener
      // that will call the handler if the click is outside the trap

      if (getOption({}, 'clickOutsideDeactivates')) {
        doc.addEventListener('pointerdown', checkPointerDown, {
          capture: true,
          passive: false
        });
      }

      doc.addEventListener('focusin', checkFocusIn, true);
      doc.addEventListener('keydown', checkKey, {
        capture: true,
        passive: false
      });
      return trap;
    }

    function removeListeners() {
      if (!state.active) {
        return;
      }

      doc.removeEventListener('pointerdown', checkPointerDown, {
        capture: true,
        passive: false
      });
      doc.removeEventListener('focusin', checkFocusIn, true);
      doc.removeEventListener('keydown', checkKey, {
        capture: true,
        passive: false
      });
      return trap;
    }

    return trap;
  };

  return createFocusTrap;

})));
