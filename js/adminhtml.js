;function trademe_auth_account (account_id) {
  var error_msg;

  if ($('trademe_button_auth_' + account_id).hasClassName('disabled'))
    return;

  error_msg = Translator
    .translate('An error occured while retrieving response');

  new Ajax.Request(trademe.url.authenticate, {
    method: 'post',
    parameters: { account_id: account_id },
    onSuccess: function(transport) {
      var response;

      if (!transport.responseText.isJSON())
        return alert(error_msg);

      response = transport.responseText.evalJSON()

      if (response.error)
        return alert(response.message);

      if (response.ajaxRedirect)
        setLocation(response.ajaxRedirect);
    },
    onFailure: function() {
      alert(error_msg);
    }
  });
}

