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

function trademe_remove_account (account_id) {
  var error_msg;

  if ($('trademe_button_remove_' + account_id).hasClassName('disabled'))
    return;

  error_msg = Translator.translate(
    'An error occured while retrieving response'
  );

  new Ajax.Request(trademe.url.canremove, {
    method: 'post',
    parameters: { account_id: account_id },
    onSuccess: function(transport) {
      var response, remove_msg;

      if (!transport.responseText.isJSON())
        return alert(error_msg);

      response = transport.responseText.evalJSON()

      if (response.error)
        return alert(response.message);

      remove_msg = Translator.translate(
        response.hasProducts
          ? 'Some products are currently listed under this account. '
            + 'Removing the account authorisation will prevent mVentory from '
            + 'tracking those auctions. Proceed anyway?'
            : 'Are you sure?'
      );

      if (!confirm(remove_msg))
        return;

      new Ajax.Request(trademe.url.remove, {
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
    },
    onFailure: function() {
      alert(error_msg);
    }
  });
}
