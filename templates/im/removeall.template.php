<table border="0" width="80%" cellspacing="1" bgcolor="<?= $this->color['bordercolor'] ?>" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="<?= $this->color['titlebg'] ?>">
      <font size="2" class="text1" color="<?= $this->color['titletext'] ?>"><b><?= $this->question ?></b></font>
    </td>
  </tr>
  <tr>
    <td class="windowbg" bgcolor="<?= $this->color['windowbg'] ?>">
      <font size="2">
         <?= $this->locale->txt[413] ?><br />
         <!-- yes/no links -->
         <b><a href="./?sesc=<?= $this->sessionid ?>"><?= $this->locale->txt[163] ?></a> - <a href="../"><?= $this->locale->txt[164] ?></a></b>
      </font>
    </td>
  </tr>
</table>
