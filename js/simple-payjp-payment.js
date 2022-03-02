const $ = jQuery;
$(function () {
  console.log("読込");

  /**
   * aria-hidden属性を変更します
   * @param event
   */
  const changeAriaHidden = (inputElement) => {
    // const inputElement = event.target;
    // const liveRegionElement = inputElement.parentNode.querySelector(
    //   "span[role='status']"
    // );
    const liveRegionElement = $(inputElement).next(
      "span[role='status']"
    );

    if (liveRegionElement == null) {
      return;
    }
    if (inputElement.validity.valid) {
      // inputタグのバリデーションがvalidならばOKメッセージを表示
      $(liveRegionElement)
        .children(".errorMessage")
        .attr("aria-hidden", "true");
    } else {
      // inputタグのバリデーションがinvalidならばエラーメッセージを表示
      $(liveRegionElement)
        .children(".errorMessage")
        .attr("aria-hidden", "true");
      let errorMessageElement;
      if (inputElement.validity.valueMissing) {
        errorMessageElement = $(liveRegionElement)
        .children(".errorMessage.empty");
      } else if (inputElement.validity.patternMismatch) {
        errorMessageElement = $(liveRegionElement)
        .children(".errorMessage.pattern");
      }

      $(errorMessageElement)
        .attr("aria-hidden", "false");
    }
  };

  $(".simplepayjppayment-container form input[type!=hidden][type!=button]")
  .each((index, element) => {
    console.log(element);
    $(element).on("input", (event) => changeAriaHidden(element));
  });
});
