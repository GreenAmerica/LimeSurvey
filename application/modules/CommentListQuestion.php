<?php
class CommentListQuestion extends ListQuestion
{
    public function getAnswerHTML()
    {
        global $maxoptionsize, $thissurvey;
        $clang=Yii::app()->lang;
        $dropdownthreshold = Yii::app()->getConfig("dropdownthreshold");

        if ($thissurvey['nokeyboard']=='Y')
        {
            includeKeypad();
            $kpclass = "text-keypad";
        }
        else
        {
            $kpclass = "";
        }

        $checkconditionFunction = "checkconditions";

        $answer = '';

        $aQuestionAttributes = $this->getAttributeValues();
        if (!isset($maxoptionsize)) {$maxoptionsize=35;}

        //question attribute random order set?
        if ($aQuestionAttributes['random_order']==1) {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY ".dbRandom();
        }
        //question attribute alphasort set?
        elseif ($aQuestionAttributes['alphasort']==1)
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY answer";
        }
        //no question attributes -> order by sortorder
        else
        {
            $ansquery = "SELECT * FROM {{answers}} WHERE qid=$this->id AND language='".$_SESSION['survey_'.Yii::app()->getConfig('surveyID')]['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
        }

        $ansresult=Yii::app()->db->createCommand($ansquery)->query();
        $anscount = $ansresult->getRowCount();


        $hint_comment = $clang->gT('Please enter your comment here');
        if ($aQuestionAttributes['use_dropdown']!=1 && $anscount <= $dropdownthreshold)
        {
            $answer .= '<div class="list">
            <ul class="answers-list radio-list">
            ';

            foreach ($ansresult->readAll() as $ansrow)
            {
                $check_ans = '';
                if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == $ansrow['code'])
                {
                    $check_ans = CHECKED;
                }
                $answer .= '		<li class="answer-item radio-item">
                <input type="radio" name="'.$this->fieldname.'" id="answer'.$this->fieldname.$ansrow['code'].'" value="'.$ansrow['code'].'" class="radio" '.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
                <label for="answer'.$this->fieldname.$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
                </li>
                ';
            }

            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                if ((!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == '') ||($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == ' ' ))
                {
                    $check_ans = CHECKED;
                }
                elseif (($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] != ''))
                {
                    $check_ans = '';
                }
                $answer .= '		<li class="answer-item radio-item noanswer-item">
                <input class="radio" type="radio" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" value=" " onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)"'.$check_ans.' />
                <label for="answer'.$this->fieldname.'" class="answertext">'.$clang->gT('No answer').'</label>
                </li>
                ';
            }

            $fname2 = $this->fieldname.'comment';
            if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
            // --> START NEW FEATURE - SAVE
            //    --> START ORIGINAL
            //        $answer .= "\t<td valign='top'>\n"
            //                 . "<textarea class='textarea' name='$this->fieldnamecomment' id='answer$this->fieldnamecomment' rows='$tarows' cols='30'>";
            //    --> END ORIGINAL
            $answer .= '	</ul>
            </div>

            <p class="comment answer-item text-item">
            <label for="answer'.$this->fieldname.'comment">'.$hint_comment.':</label>

            <textarea class="textarea '.$kpclass.'" name="'.$this->fieldname.'comment" id="answer'.$this->fieldname.'comment" rows="'.floor($tarows).'" cols="30" >';
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2])
            {
                $answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]);
            }
            $answer .= '</textarea>
            </p>

            <input class="radio" type="hidden" name="java'.$this->fieldname.'" id="java'.$this->fieldname.'" value="'.$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname].'" />
            ';
        }
        else //Dropdown list
        {
            // --> START NEW FEATURE - SAVE
            $answer .= '<p class="select answer-item dropdown-item">
            <select class="select" name="'.$this->fieldname.'" id="answer'.$this->fieldname.'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" >
            ';
            // --> END NEW FEATURE - SAVE
            foreach ($ansresult->readAll() as $ansrow)
            {
                $check_ans = '';
                if ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == $ansrow['code'])
                {
                    $check_ans = SELECTED;
                }
                $answer .= '		<option value="'.$ansrow['code'].'"'.$check_ans.'>'.$ansrow['answer']."</option>\n";

                if (strlen($ansrow['answer']) > $maxoptionsize)
                {
                    $maxoptionsize = strlen($ansrow['answer']);
                }
            }
            if ($this->mandatory != 'Y' && SHOW_NO_ANSWER == 1)
            {
                if ((!$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == '') ||($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] == ' '))
                {
                    $check_ans = SELECTED;
                }
                elseif ($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] || $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname] != '')
                {
                    $check_ans = '';
                }
                $answer .= '<option class="noanswer-item" value=""'.$check_ans.'>'.$clang->gT('No answer')."</option>\n";
            }
            $answer .= '	</select>
            </p>
            ';
            $fname2 = $this->fieldname.'comment';
            if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
            if ($tarows > 15) {$tarows=15;}
            $maxoptionsize=$maxoptionsize*0.72;
            if ($maxoptionsize < 33) {$maxoptionsize=33;}
            if ($maxoptionsize > 70) {$maxoptionsize=70;}
            $answer .= '<p class="comment answer-item text-item">
            <label for="answer'.$this->fieldname.'comment">'.$hint_comment.':</label>
            <textarea class="textarea '.$kpclass.'" name="'.$this->fieldname.'comment" id="answer'.$this->fieldname.'comment" rows="'.$tarows.'" cols="'.$maxoptionsize.'" >';
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]) && $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2])
            {
                $answer .= str_replace("\\", "", $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2]);
            }
            $answer .= '</textarea>
            <input class="radio" type="hidden" name="java'.$this->fieldname.'" id="java'.$this->fieldname.'" value="'.$_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$this->fieldname].'" /></p>';
        }
        return $answer;
    }
        
    //public function getTitle() - inherited
    
    //public function getHelp() - inherited
    
    public function availableAttributes()
    {
        return array("alphasort","statistics_showgraph","statistics_graphtype","hide_tip","hidden","page_break","public_statistics","random_order","parent_order","use_dropdown","scale_export","random_group");
    }

    public function questionProperties()
    {
        $clang=Yii::app()->lang;
        return array('description' => $clang->gT("List with comment"),'group' => $clang->gT("Single choice questions"),'subquestions' => 0,'hasdefaultvalues' => 1,'assessable' => 1,'answerscales' => 1);
    }
}
?>