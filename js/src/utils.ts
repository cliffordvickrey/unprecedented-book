function formToObject(form: HTMLFormElement): any {
  const formData = new FormData(form);
  const object: any = {};

  formData.forEach((value, key) => {
    const strValue = value as string;

    let prop = key;

    if (prop.endsWith("[]")) {
      prop = prop.slice(0, -2);

      if (!object.hasOwnProperty(prop)) {
        object[prop] = [];
      }
    }

    if (object.hasOwnProperty(prop)) {
      if (!Array.isArray(object[prop])) {
        object[prop] = [object[prop]];
      }
      object[prop].push(strValue);
    } else {
      object[prop] = strValue;
    }
  });

  return object;
}

function queryString(object: any): string {
  const params: string[] = [];

  for (const [key, value] of Object.entries(object)) {
    const strValue = value as string;

    if (Array.isArray(value)) {
      value.forEach((v) => {
        params.push(`${encodeURIComponent(key)}[]=${encodeURIComponent(v)}`);
      });
    } else {
      params.push(`${encodeURIComponent(key)}=${encodeURIComponent(strValue)}`);
    }
  }

  return params.join("&");
}

export function formToUrl(form: HTMLFormElement, extras: object = {}): any {
  return "./?" + queryString({ ...formToObject(form), ...extras });
}
