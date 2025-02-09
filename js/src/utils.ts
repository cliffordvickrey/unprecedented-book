function formToObject(form: HTMLFormElement): any {
  const formData = new FormData(form);
  const object: any = {};

  formData.forEach((value, key) => {
    const strValue = value as string;

    if (object.hasOwnProperty(key)) {
      if (!Array.isArray(object[key])) {
        object[key] = [object[key]];
      }
      object[key].push(strValue);
    } else {
      object[key] = strValue;
    }
  });

  return object;
}

function queryString(object: any) {
  return new URLSearchParams(object).toString();
}

export function formToQueryString(
  form: HTMLFormElement,
  extras: object = {},
): any {
  return "./?" + queryString({ ...formToObject(form), ...extras });
}
