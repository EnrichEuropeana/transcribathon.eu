const { xml2js, json2xml } = require("xml-js");
const jp = require("jmespath");
const simplify = require("simplify-js");

const XML2JS = {
  DEFAULT: {
    compact: false,
    trim: false,
    alwaysArray: true,
    alwaysChildren: true,
    attributeValueFn: parseAttributeValue,
  },
  TEST: {
    compact: false,
    attributeValueFn: parseAttributeValue,
  },
};

const SIMPLIFY_TOLERANCE = 25; // pixels

function parsePointsAttribute(string = "") {
  return string
  .split(/\s+/)
  .map(string => string.split(/\s*,\s*/).map(x => parseInt(x)))
  .map(([x, y]) => ({ x, y }))
  // return simplify(
  //   string
  //     .split(/\s+/)
  //     .map((string) => string.split(/\s*,\s*/).map((x) => parseInt(x)))
  //     .map(([x, y]) => ({ x, y })),
  //   SIMPLIFY_TOLERANCE
  // );
}

function reverseParsePointsAttribute(values) {
  var pointString = "";
  for (var point in values.points) {
    var temp = values.points[point];
    pointString += temp.x + "," + temp.y + " ";
  }
  values.points = pointString.replace(/\s*$/, "");
}

function parseCustomAttribute(string) {
  if (string === "") return [];

  return string.split("} ").reduce((results, string) => {
    const name = string.split(" {")[0];
    let attrs = string.split(" {")[1];

    if (attrs.indexOf("}")) attrs = attrs.replace("}", "");

    var key, value;

    return results.concat({
      name: name.trim(),
      attributes: attrs.split(";").reduce((attrs, string) => {
        string = string.trim();
        if (string) {
          key = string.split(":")[0].trim();
          value = decodeURIComponent(
            JSON.parse(
              '"' + string.split(":")[1].trim().replace('"', '\\"') + '"'
            )
          );
          if (key === "index" || key === "offset" || key === "length") {
            attrs[key] = parseInt(value);
          } else if (
            key === "continued" ||
            key === "superscript" ||
            key === "bold" ||
            key === "italic" ||
            key === "subscript"
          ) {
            attrs[key] = Boolean(value);
          } else attrs[key] = value;
        }
        return attrs;
      }, {}),
    });
  }, []);
}

function reverseParseparseCustomAttribute(value) {
  var customString = "";
  for (var custom in value.custom) {
    var temp = value.custom[custom];
    customString +=
      temp.name +
      " " +
      JSON.stringify(temp.attributes).replaceAll('"', "").replaceAll(",", ";") +
      " ";
  }
  value.custom = customString;
}

function parseAttributeValue(value, name) {
  switch (name) {
    case "points":
      return parsePointsAttribute(value);
    case "custom":
      return parseCustomAttribute(value);
    default:
      return value;
  }
}

function reverseParseAttributeValue(values, name) {
  if (values.points) {
    reverseParsePointsAttribute(values);
  }
  if (values.custom) {
    reverseParseparseCustomAttribute(values);
  }
  if (values.imgUrl) {
    values.imgUrl = values.imgUrl.replaceAll("&", "&amp;");
  }
  switch (name) {
    case "points":
      return reverseParsePointsAttribute(values);
    case "custom":
      return reverseParseparseCustomAttribute(values);
    default:
      return values;
  }
}

export function getObjects(obj, key, val) {
  var objects = [];
  for (var i in obj) {
    if (!obj.hasOwnProperty(i)) continue;
    if (typeof obj[i] == "object") {
      objects = objects.concat(getObjects(obj[i], key, val));
    } else if ((i == key && obj[i] == val) || (i == key && val == "")) {
      objects.push(obj);
    } else if (obj[i] == val && key == "") {
      if (objects.lastIndexOf(obj) == -1) {
        objects.push(obj);
      }
    }
  }
  return objects;
}

function replaceLine(obj, key, val, textString, customObjArr) {
  for (var i in obj) {
    if (!obj.hasOwnProperty(i)) continue;
    if (typeof obj[i] == "object") {
      if (obj[i] && obj[i].name == "TextLine" && obj[i].attributes) {
        if (obj[i].attributes.id == val) {
          obj[i].attributes.custom = customObjArr;
          var elementArr = {
            elements: [{ text: "", type: "text" }],
            name: "Unicode",
            type: "element",
          };
          obj[i].elements[2].elements[0] = elementArr;
          obj[i].elements[2].elements[0].elements[0].text = textString;
          return obj[i];
        }
      }
      replaceLine(obj[i], key, val, textString, customObjArr);
    } else if ((i == key && obj[i] == val) || (i == key && val == " ")) {
      obj[i].attributes.custom = customObjArr;
      obj[i].elements[2].elements[0].elements[0].text = textString;
      return obj[i];
    }
  }
}

export function getValues(obj, key) {
  var objects = [];
  for (var i in obj) {
    if (!obj.hasOwnProperty(i)) continue;
    if (typeof obj[i] == "object") {
      objects = objects.concat(getValues(obj[i], key));
    } else if (i == key) {
      objects.push(obj[i]);
    }
  }
  return objects;
}

export function getKeys(obj, val) {
  var objects = [];
  for (var i in obj) {
    if (!obj.hasOwnProperty(i)) continue;
    if (typeof obj[i] == "object") {
      objects = objects.concat(getKeys(obj[i], val));
    } else if (obj[i] == val) {
      objects.push(i);
    }
  }
  return objects;
}

export function parseEditorOutput(editorJson, globalJson) {
  var editorLineArr = getObjects(editorJson, "type", "line");
  for (var lineIndex in editorLineArr) {
    var line = editorLineArr[lineIndex];
    var customObjArr = [];
    customObjArr.push(line.attrs.line.attributes.custom[0]);
    var textString = "";
    if (line.content == undefined) {
      replaceLine(
        globalJson,
        "id",
        line.attrs.line.attributes.id,
        " ",
        customObjArr
      );
    }
    for (var contentIndex in line.content) {
      var lineContent = line.content[contentIndex];
      var text = lineContent.text;
      if (lineContent.marks) {
        for (var markIndex in lineContent.marks) {
          var mark = lineContent.marks[markIndex];
          var attributes = {};
          var name = mark.type;
          var offset = textString.length;
          var length = text.length;
          if (mark.attrs) {
            attributes = mark.attrs.tag.attributes;
          }

          if (attributes.offset === undefined) {
            //TODO check for continued
            attributes.offset = offset;
            attributes.length = length;
          }

          switch (name) {
            case "strike":
              name = "textStyle";
              attributes.strikethrough = true;
              break;
            case "underline":
              name = "textStyle";
              attributes.underlined = true;
              break;
            case "subscript":
              name = "textStyle";
              attributes.subscript = true;
              break;

            case "superscript":
              name = "textStyle";
              attributes.superscript = true;
              break;
          }

          customObjArr.push({ name: name, attributes: attributes });
        }
      }
      textString += text;
      replaceLine(
        globalJson,
        "id",
        line.attrs.line.attributes.id,
        textString,
        customObjArr
      );
    }
  }
  return globalJson;
}

export function parseToXML(json) {
  var options = {
    compact: false,
    ignoreComment: true,
    spaces: 4,
    sanitize: false,
    attributesFn: reverseParseAttributeValue,
  };
  var result = json2xml(json, options);
  return result;
}

export function parseJsonContent(json) {
  const result = jp.search(
    json,
    "{ declaration: declaration,PcGts: elements[?name == 'PcGts'].{type: name,attributes: attributes,Page: elements[?name == 'Page'].{type: name,attributes: attributes,elements: elements[?name == 'TextRegion' || name == 'TableRegion'].{type: name,attributes: attributes,geometry: {coords: elements[?name == 'Coords'].attributes.points | [0]},elements: elements[?name == 'TextLine' || name == 'TableCell'].{type: name,attributes: attributes,geometry: {coords: elements[?name == 'Coords'].attributes.points | [0],baseline: elements[?name == 'Baseline'].attributes.points | [0]},elements: [elements[?name == 'TextEquiv' || name == 'TextLine'].{ elements:[elements[?name == 'Unicode' || name == 'TextEquiv'].[elements[?type == 'text' || name == 'Unicode']] | []], attributes : attributes, geometry: {coords: elements[?name == 'Coords'].attributes.points | [0]}}] | [] | [] | []} | []} } | [0]} | [0]}"
  );
  return result.PcGts.Page;
}

export function parsePageContent(xml, type = "DEFAULT") {
  const js = xml2js(xml, XML2JS[type]);
  var json = JSON.parse(JSON.stringify(js));
  this.$store.dispatch("content/setXmlModel", {
    xmlModel: json,
  });
  const result = jp.search(
    js,
    "{ declaration: declaration,PcGts: elements[?name == 'PcGts'].{type: name,attributes: attributes,Page: elements[?name == 'Page'].{type: name,attributes: attributes,elements: elements[?name == 'TextRegion' || name == 'TableRegion'].{type: name,attributes: attributes,geometry: {coords: elements[?name == 'Coords'].attributes.points | [0]},elements: elements[?name == 'TextLine' || name == 'TableCell'].{type: name,attributes: attributes,geometry: {coords: elements[?name == 'Coords'].attributes.points | [0],baseline: elements[?name == 'Baseline'].attributes.points | [0]},elements: [elements[?name == 'TextEquiv' || name == 'TextLine'].{ elements:[elements[?name == 'Unicode' || name == 'TextEquiv'].[elements[?type == 'text' || name == 'Unicode']] | []], attributes : attributes, geometry: {coords: elements[?name == 'Coords'].attributes.points | [0]}}] | [] | [] | []} | []} } | [0]} | [0]}"
  );
  return result.PcGts.Page;
}
