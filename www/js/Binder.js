/**
 * Binder - javascript library used for detection of binding data from area to php via ajax request.
 * 
 * Distributed under Beerware licence (https://en.wikipedia.org/wiki/Beerware). Beerware rev.42:
 * <matej@penzion-rataje.eu> wrote this file. As long as you retain this notice you can do whatever you want with this stuff. If we meet some day, and you think this stuff is worth it, you can buy me a beer in return. Poul-Henning Kamp
 * 
 * Usage = After document load, call function Binder(settings) , where settings is object containing default values. Settings object must contain object area, specifying html caller element containing one row.
 * Caller element should contain attributes data-binder-table and data-binder-id, specifying table name and id of record.
 * Caller element may contain save button, specified by its class (default binder-save-btn)
 * Caller element may contain delete button, specified by its class (default binder-delete-btn)
 * Caller element must contain database field elements, containing the data - INPUT, SELECT or TEXTAREA fields. Their name should correspond to its database field name. Each field element should also have attribute data-value containing original value, loaded from database.
 * 
 * Optional settings attribute is saveAllSelector, which can contain selector for specifying a button that saves all visible binder records.
 * 
 * Every element having attribute data-value is being treated as db field element. Whenever its changed, binder checks for all changes in the record (differences between the actual value and data-value attribute). If there is at least one change, binder changes the class of save button from btn-primary (btn-secondary etc. corresponding to Bootstrap v4 class names) to btn-outline-* to mark change saveable.
 * After clicking on save button, data with changed fields are sent via ajax to backend url. Url is taken from save button's href attribute. Object is having properties id, table and changes. Changes property contains key value pairs of changed items in records.
 * After succesfull save, save button class is reinstantized back to normal class.
 * 
 * After clicking on delete button, object, containing just id and table properties is sent to url, specified by delete button's href attribute. After succesfull ajax request, whole binder area gets deleted.
 */

window.onload = function(e){ 
    if (!window.jQuery) {
        throw "Jquery is neccessary to run Binder!";
    }
};

function Binder (settings) {
    this.area = $(settings.area);
    this.TABLE_NAME_ATTRIBUTE = !settings.tableNameAttribute ? "data-binder-table" : settings.tableNameAttribute;
    this.RECORD_ID_ATTRIBUTE = !settings.recordIdAttribute ? "data-binder-id" : settings.recordIdAttribute;
    this.ORIGINAL_VALUE_ATTRIBUTE = !settings.originalValueAttribute ? "data-value" : settings.originalValueAttribute;
    this.VALIDATION_FIELD2_ATTRIBUTE = !settings.validationField2Attribute ? "data-validation-field2" : settings.validationField2Attribute;
    this.DBFIELD_NAME_ATTRIBUTE = !settings.dbFieldNameAttribute ? "name" : settings.dbFieldNameAttribute;
    this.SPINNER_CLASS = !settings.spinClass ? "fa-spin" : settings.spinClass;
    this.SAVE_BTN_CLASS = !settings.saveBtnClass ? "binder-save-btn" : settings.saveBtnClass;
    this.DELETE_BTN_CLASS = !settings.deleteBtnClass ? "binder-delete-btn" : settings.deleteBtnClass;
    this.BUTTON_CHECKED_CLASS = !settings.checkedBtnClass ? "active" : settings.checkedBtnClass;
    this.SAVE_ALL_BTN_CLASS = !settings.saveAllSelector ? "binder-save-all-btn" : settings.saveAllSelector;
    this.saveButtons = this.area.find("." + this.SAVE_BTN_CLASS);
    this.saveAllButtons = $("." + this.SAVE_ALL_BTN_CLASS);
    this.isValid = !settings.isValid ? true : settings.isValid;
    this.isValidated = false;
    this.changed = false;
    this.bindChangeEvents();
    this.bindSaveEvent();
    this.bindSaveAllEvent();
    this.bindDeleteEvent();
    this.checkNewRow();
}

Binder.prototype.checkNewRow = function () {
    var objId = this.area.attr(this.RECORD_ID_ATTRIBUTE);
    if(objId == -1){
        this.changed = true;
        this.changeSaveButtonClass(true);
        this.changeSaveAllButtonClass(true);
        this.area.find("[" + this.ORIGINAL_VALUE_ATTRIBUTE + "]").attr(this.ORIGINAL_VALUE_ATTRIBUTE,"null");
    }
}

Binder.prototype.bindChangeEvents = function () {
    var binderObj = this;
    var targets = binderObj.area.find("[" + this.ORIGINAL_VALUE_ATTRIBUTE + "]");
    if (targets.length > 0) {
        targets.each(function () {
            if ($(this).prop("tagName") === "BUTTON") {
                $(this).off("click");
                $(this).click(function(){
                    binderObj.getChanges();
                    binderObj.changeSaveButtonClass(binderObj.changed);
                    binderObj.changeSaveAllButtonClass(binderObj.changed);
                });
            } else {
                $(this).off("change");
                $(this).change(function(){
                    binderObj.getChanges();
                    binderObj.changeSaveButtonClass(binderObj.changed);
                    binderObj.changeSaveAllButtonClass(binderObj.changed);
                });
            }
        });
    }
};

Binder.prototype.bindSaveEvent = function () {
    var binderObj = this;
    if (this.saveButtons.length > 0) {
        this.saveButtons.each(function () {
            $(this).off("click");
            $(this).click(function () {
                binderObj.extractBind();
                binderObj.save($(this));
            });
        });
    }
};

Binder.prototype.bindSaveAllEvent = function () {
    var binderObj = this;
    if (this.saveAllButtons.length > 0) {
        this.saveAllButtons.each(function () {
            if($(this).data("binders")){
                var binders = $(this).data("binders");
                binders.push(binderObj);
            } else {
                $(this).data("binders", [binderObj]);
            }
            $(this).off("click");
            $(this).click(function () {
                binderObj.saveAll($(this));
            });
        });
    }
};

Binder.prototype.bindDeleteEvent = function () {
    var binderObj = this;
    var targets = binderObj.area.find("." + this.DELETE_BTN_CLASS);
    if (targets.length > 0) {
        targets.each(function () {
            $(this).off("click");
            $(this).click(function () {
                binderObj.extractBind();
                binderObj.delete($(this));
            });
        });
    }
};

Binder.prototype.save = function (caller) {
    if (typeof this.area == "undefined")
        throw "Binder performing error - undefined area!";
    if (typeof this.bind == "undefined")
        throw "Binder performig error - undefined binding object!";
    if (!this.isValidated)
        alert("Validation failing, saving disabled!");
    var binderObj = this;
    if (!($.isEmptyObject(binderObj.bind.changes))) {
        binderObj.disableSaveButtons(true, true);
        binderObj.disableSaveAllButtons(true, true);
        $.nette.ajax({
            url: caller.attr("href"),
            method: 'POST',
            data: binderObj.bind,
            complete: function (payload) {
                binderObj.commit();
                binderObj.disableSaveAllButtons(false);
                binderObj.changeSaveAllButtonClass();
            }
        });
    }
};

Binder.prototype.disableSaveButtons = function(disable, spin = false){
    var binderObj = this;
    this.saveButtons.each(function(){
        binderObj.disableBtn($(this), disable, spin);
    });
}

Binder.prototype.disableSaveAllButtons = function(disable, spin = false){
    var binderObj = this;
    this.saveAllButtons.each(function(){
        binderObj.disableBtn($(this), disable, spin);
    });
}

Binder.prototype.saveAll = function (caller) {
    var allBinders = $(caller).data("binders");
    var index;
    if (allBinders.length > 0) {
        var data = [];
        for (index = 0; index < allBinders.length; ++index) {
            var binderObj = allBinders[index];
            binderObj.extractBind();
            if (!binderObj.isValidated)
                throw "Validation failing, saving disabled!";
            if (!($.isEmptyObject(binderObj.bind.changes))) {
                binderObj.disableSaveButtons(true, true);
                binderObj.changeSaveButtonClass(true);
                data.push(binderObj.bind);
            }
        }
        if (data.length > 0) {
            binderObj.disableSaveAllButtons(true, true);
            binderObj.changeSaveAllButtonClass(true);
            $.nette.ajax({
                url: caller.attr("href"),
                method: 'POST',
                data: {binders: data},
                complete: function (payload) {
                    allBinders.forEach(function(binderObj){
                        binderObj.commit();
                    });
                    binderObj.disableSaveAllButtons(false);
                    binderObj.changeSaveAllButtonClass(false);
                }
            });
        }
    }
};

Binder.prototype.delete = function(caller){
    if(typeof this.area == "undefined")
        throw "Binder performing error - undefined area!";
    if(typeof this.bind == "undefined")
        throw "Binder performig error - undefined binding object!";
    var binderObj = this;
    binderObj.disableBtn(caller, true, true);
    $.nette.ajax({
        url: caller.attr("href"),
        method: 'POST',
        data: binderObj.bind,
        complete: function (payload) {
            binderObj.area.remove();
        }
    });
};

Binder.prototype.commit = function () {
    var binderObj = this;
    var targets = binderObj.area.find("[" + binderObj.ORIGINAL_VALUE_ATTRIBUTE + "]");
    if (targets.length > 0) {
        targets.each(function () {
            $(this).attr(binderObj.ORIGINAL_VALUE_ATTRIBUTE, binderObj.getValue($(this)));
        });
    }
    binderObj.changeSaveButtonClass(false);
    binderObj.disableSaveButtons(false);
    this.changed = false;
};

Binder.prototype.getButtonClass = function(button){
    var classArray = button.attr("class").split(" ");
    var lookForClasses = ["primary", "secondary", "success", "danger", "warning", "info", "light", "dark"];
    var detectedClass = null;
    lookForClasses.forEach(function(cls){
        if((index = classArray.indexOf("btn-" + cls)) !== -1){
            return detectedClass = classArray[index];
        }
        if((index = classArray.indexOf("btn-outline-" + cls)) !== -1){
            return detectedClass = classArray[index];
        }
    });
    return detectedClass;
}

Binder.prototype.changeSaveButtonClass = function (commitPending){
    var binderObj = this;
    var targets = binderObj.saveButtons;
    var cls, newCls;
    if (targets.length > 0) {
        targets.each(function () {
            cls = binderObj.getButtonClass($(this));
            var isOutlined = cls.indexOf("btn-outline") !== -1;
            if (commitPending) {
                if(isOutlined) return;
                newCls = cls.replace("btn", "btn-outline");
            } else {
                if(!isOutlined) return;
                newCls = cls.replace("btn-outline", "btn");
            }
            $(this).removeClass(cls);
            $(this).addClass(newCls);
        });
    }
};

Binder.prototype.changeSaveAllButtonClass = function (commitPending) {
    var binderObj = this;
    var cls, newCls;
    if (typeof commitPending == "undefined") {
        commitPending = false;
        var allBinders = $(binderObj.saveAllButtons[0]).data("binders");
        if (allBinders && allBinders.length > 0) {
            for (index = 0; index < allBinders.length; ++index) {
                var binderObj = allBinders[index];
                if (binderObj.changed) {
                    commitPending = true;
                    break;
                }
            }
        }
    }
    binderObj.saveAllButtons.each(function () {
        cls = binderObj.getButtonClass($(this));
        var isOutlined = cls.indexOf("btn-outline") !== -1;
        if (commitPending) {
            if(isOutlined) return;
            newCls = cls.replace("btn", "btn-outline");
        } else {
            if(!isOutlined) return;
            newCls = cls.replace("btn-outline", "btn");
        }
        $(this).removeClass(cls);
        $(this).addClass(newCls);
    });
}

Binder.prototype.extractBind = function() {
    var obj = {};
    var objId = this.area.attr(this.RECORD_ID_ATTRIBUTE);
    var tableName = this.area.attr(this.TABLE_NAME_ATTRIBUTE);
    var changes = this.getChanges();
    obj.id = objId;
    obj.table = tableName;
    obj.changes = changes;
    this.bind  = obj;
};

Binder.prototype.getChanges = function () {
    var values = {};
    var binderObj = this;
    var targets = binderObj.area.find("[" + binderObj.ORIGINAL_VALUE_ATTRIBUTE + "]");
    if (targets.length > 0) {
        this.isValidated = true;
        targets.each(function () {
            var tagName = $(this).prop("tagName");
            var name = binderObj.parseNameFromElement($(this));
            var value = binderObj.getValue($(this));
            if (["INPUT", "SELECT", "TEXTAREA", "BUTTON"].indexOf(tagName) > -1) {
                if (binderObj.isChanged($(this))) {
                    values[name] = $.isArray(value) && $.isEmptyObject(value) ? "" : value;
                }
            } else {
                throw "Unexpected tag " + tagName + " found, containing binded values";
            }
        });
    }
    binderObj.changed = !$.isEmptyObject(values);
    return values;
};

Binder.prototype.getValue = function(element){
    var binderObj = this;
    var bracketIndex = element.attr(binderObj.DBFIELD_NAME_ATTRIBUTE).indexOf("[");
    if(bracketIndex == -1){
        //not part of a group
        return binderObj.parseValueFromElement(element);
    } else {
        return binderObj.parseValueFromGroupOfElements(element);
    }
};

Binder.prototype.parseNameFromElement = function (element){
    var binderObj = this;
    var bracketBegin = element.attr(binderObj.DBFIELD_NAME_ATTRIBUTE).indexOf("[");
    if(bracketBegin == -1){
        return element.attr(binderObj.DBFIELD_NAME_ATTRIBUTE);
    } else {
        return element.attr(binderObj.DBFIELD_NAME_ATTRIBUTE).substr(0, bracketBegin);
    }
}

/**
 * This function checks all elements in the same group. It expects that the elements in group are boolean and returns array of values of all of them
 * @param element
 * @returns array of values
 */
Binder.prototype.parseValueFromGroupOfElements = function (element) {
    var binderObj = this;
    var bracketBegin, bracketEnd, value;
    var groupName = binderObj.parseNameFromElement(element);
    var targets = binderObj.area.find("[" + binderObj.DBFIELD_NAME_ATTRIBUTE + "^=" + groupName + "]");
    var values = [];
    if (targets.length > 0) {
        targets.each(function () {
            bracketBegin = $(this).attr(binderObj.DBFIELD_NAME_ATTRIBUTE).indexOf("[");
            bracketEnd = $(this).attr(binderObj.DBFIELD_NAME_ATTRIBUTE).indexOf("]");
            value = $(this).attr(binderObj.DBFIELD_NAME_ATTRIBUTE).substr(bracketBegin + 1, bracketEnd - bracketBegin - 1);
            if(binderObj.parseValueFromElement($(this)))
                values.push(value);
        });
    }
    return values;
};

Binder.prototype.parseValueFromElement = function(element){
    if(element.is(":checkbox")) return element.is(":checked") ? "true" : "false";
    if(element.prop("tagName") == "BUTTON") return element.hasClass(this.BUTTON_CHECKED_CLASS);
    return element.val();
}

Binder.prototype.isChanged = function (element) {
    var binderObj = this;
    var changed = element.attr(binderObj.ORIGINAL_VALUE_ATTRIBUTE) != this.getValue(element);
    if (changed) {
        var value2 = element.attr(binderObj.VALIDATION_FIELD2_ATTRIBUTE);
        if (typeof value2 !== typeof undefined && value2 !== false) {
            binderObj.validate(element, binderObj.getValue(binderObj.area.find("[name='" + value2 + "']")));
        } else {
            binderObj.validate(element);
        }
    }
    return changed;
};

Binder.prototype.disableBtn = function (btn, disable, spin = false) {
    if (btn.length > 0) {
        if (disable) {
            if(spin){
                btn.find("svg[data-fa-i2svg]").addClass(this.SPINNER_CLASS);
            }
            btn.prop("disabled", true);
            btn.attr("disabled", "disabled");
        } else {
            btn.find("svg[data-fa-i2svg]").removeClass(this.SPINNER_CLASS);
            btn.prop("disabled", false);
            btn.removeAttr("disabled");
        }
    }
};

Binder.prototype.validate = function(element, value2 = null) {
    var binderObj = this;
    var valid = true;
    if(typeof(binderObj.isValid) == "boolean"){
        valid = binderObj.isValid;
    } else {
        var name = binderObj.parseNameFromElement(element);
        var value = binderObj.getValue(element);
        valid = binderObj.isValid(name, value, value2);
    }

    if (!valid) {
        this.isValidated = false;
        element.addClass("is-invalid");
    } else {
        element.removeClass("is-invalid");
    }
};