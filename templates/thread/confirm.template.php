      <p>Ќажима€ кнопку "ѕодтверждаю", € признаю, что данное сообщение создано по всем правилам
       данного ‘орума и оно не €вл€етс€ спамом или флудом. «а нарушение данных правил
       € несу полную ответственность.</p>
      <p><b>я ознакомлен с требованием данного форума о том, что € должен пользоватьс€ одним и тем же ником</b> дл€ ответов на этом форуме. —огласен с тем, что
      в противном случае мне может быть ограничена возможность ответа на форуме.</p>

      <form action="." name="cform" id="cform" method="POST">
        <input type="hidden" name="cconfirm" value="<?= $this->cValue ?>">
        <input type="hidden" name="input<?= $this->cValue%7 ?>" value="YES">
        <input type="hidden" name="sc" value="<?= $this->app->session->id ?>">
        <input type="button" value="ќтменить" onclick="location.href='<?= SITE_ROOT ?>'">
        <input type="submit" value="ѕодтверждаю" onclick="document.cform.input<?= $this->cValue%7 ?>.value='<?= $this->cValue ?>'">
      </form>
