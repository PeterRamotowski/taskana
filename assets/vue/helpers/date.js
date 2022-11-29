export const formatDate = function(date, onlyDate = false) {
  let dateInstance = date;

  if (!date) {
    return;
  }

  if (!(date instanceof Date)) {
    dateInstance = new Date(date);
  }

  let formatted = dateInstance.getDate().toString().padStart(2, '0')
    .concat(
      '.',
      (dateInstance.getMonth() + 1).toString().padStart(2, '0'),
      '.',
      dateInstance.getFullYear().toString(),
    );

  if (onlyDate !== true) {
    formatted = formatted.concat(
      ', ',
      dateInstance.getHours().toString().padStart(2, '0'),
      ':',
      dateInstance.getMinutes().toString().padStart(2, '0'),
    );
  }

  return formatted;
};

export const formatRequestDate = function(date) {
  const year = date.getFullYear();
  let month = date.getMonth() + 1;
  let day = date.getDate();

  if (month <= 9) {
    month = '0'.concat(month.toString());
  }

  if (day <= 9) {
    day = '0'.concat(day.toString());
  }

  return year.toString().concat('-', month, '-', day);
};
