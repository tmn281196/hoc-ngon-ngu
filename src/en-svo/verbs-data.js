/* 16 câu chính, chú giải bằng tay, cộng thêm các câu phụ trong ex[].

   Chỉ số trong DATA là cố định: OMNI[].gi trong omni-data.js trỏ thẳng vào đây,
   nên đừng đánh số lại — trang chỉ lấy một lát qua GROUPS.from/to.

   Trang omnibus.html dựng lát 0..12, tức mười ba động từ đặc biệt. Ba câu chót
   13..15 — want, smile, write — không có thanh bên nào xổ ra, nhưng giữ lại vì
   chú giải token của chúng là bộ duy nhất trong repo cho bổ ngữ mệnh đề, câu
   không tân ngữ, và thể bị động; nguyên văn ba câu đó vẫn nằm trong bảng tra ở
   reference.html. Muốn cho hiện lại thì nới lát của trang thành to:16.

   ex[] — câu thêm cho cùng một động từ, cùng hình dạng {pattern, en, vi, note,
   tokens} như câu chính. graph.js gộp [câu chính, ...ex] thành một danh sách và
   cho thanh chấm dưới sân lật qua từng câu; thanh chấm tự ẩn khi động từ chỉ có
   một câu, nên thêm hay không thêm ex đều chạy.

   Mỗi câu phụ phải vẽ ra một KHUNG KHÁC câu chính — đó mới là lý do nó tồn tại.
   Thêm một câu nữa cùng khung S V O thì đồ thị vẽ lại y hệt, chẳng dạy thêm gì.

   Nhãn func hợp lệ nằm trong FUNC_INFO của graph.js: subj, expl, verb, dObj,
   iObj, sComp, oComp, aComp, cComp, pComp, mod, adjunct, det, aux, prt, to.
   Nhãn lạ không ném lỗi nữa nhưng sẽ rơi hết về nhóm "từ phụ" — tức là sai
   thầm lặng, nên cứ bám bảng trên. Quy ước: giới từ mang chức vụ của cả cụm,
   danh từ sau nó là pComp; det trỏ vào danh từ của mình; động từ chính head:-1. */
window.SVO_VERBS = (function(){
  // Lát mặc định, dùng khi trang không tự khai SVO_PAGE.groups. Đây là nguồn
  // duy nhất định nghĩa nhóm — đừng chép lại con số vào trang.
  const GROUPS = [
    {name:"Động từ đặc biệt", from:0, to:21}
  ];

  const DATA = [
    {
      verb:"do", pattern:"S V IO DO", en:"She did me a favor.",
      vi:"Cô ấy giúp tôi một việc.",
      note:"Ghi chú <em>Do</em>: <em>do me a favor</em>. Hai tân ngữ — <strong>me</strong> là người nhận, <strong>a favor</strong> là việc được làm. <em>Do</em> thiên về quá trình; còn <em>make</em> thiên về kết quả tạo ra cái mới.",
      tokens:[
        {w:"She", pos:"PRON", func:"subj", head:1},
        {w:"did", pos:"VERB", func:"verb", head:-1},
        {w:"me", pos:"PRON", func:"iObj", head:1},
        {w:"a", pos:"DET", func:"det", head:4},
        {w:"favor", pos:"NOUN", func:"dObj", head:1}
      ],
      ex:[
        {
          pattern:"trợ động từ", en:"I don’t smoke.",
          vi:"Tôi không hút thuốc.",
          note:"Cùng chữ <em>do</em> nhưng ở đây nó <strong>không mang nghĩa “làm”</strong> — chỉ đỡ cho phủ định. Động từ chính của câu là <strong>smoke</strong>, và nó mới là chỗ cắm gốc câu.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:2},
            {w:"don’t", pos:"AUX", func:"aux", head:2},
            {w:"smoke", pos:"VERB", func:"verb", head:-1}
          ]
        },
        {
          pattern:"S V O A", en:"She does the shopping on Friday.",
          vi:"Cô ấy đi chợ vào thứ Sáu.",
          note:"<em>do</em> + danh từ chỉ việc, đúng nghĩa gốc. <strong>on Friday</strong> bỏ đi thì <em>She does the shopping</em> vẫn là câu đúng — nên nó là trạng ngữ tùy ý, khác hẳn <em>on the table</em> trong câu <em>put</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"does", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:3},
            {w:"shopping", pos:"NOUN", func:"dObj", head:1},
            {w:"on", pos:"ADP", func:"adjunct", head:1},
            {w:"Friday", pos:"PROPN", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V O A", en:"I usually do housework on Sunday morning.",
          vi:"Tôi thường làm việc nhà vào sáng Chủ nhật.",
          note:"<em>do</em> + danh từ chỉ việc nhà, nghĩa gốc. Cả <strong>usually</strong> lẫn <strong>on Sunday morning</strong> đều tháo ra được — phần xương chỉ còn <em>I do housework</em>.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:2},
            {w:"usually", pos:"ADV", func:"adjunct", head:2},
            {w:"do", pos:"VERB", func:"verb", head:-1},
            {w:"housework", pos:"NOUN", func:"dObj", head:2},
            {w:"on", pos:"ADP", func:"adjunct", head:2},
            {w:"Sunday", pos:"PROPN", func:"mod", head:6},
            {w:"morning", pos:"NOUN", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V O A", en:"They are doing research for their final project.",
          vi:"Họ đang làm nghiên cứu cho đồ án cuối khóa.",
          note:"Thì tiếp diễn: <strong>are</strong> chỉ là trợ động từ, gốc câu vẫn nằm ở <strong>doing</strong>. <em>do research</em> là một trong những cụm mà tiếng Anh dùng <em>do</em> chứ không dùng <em>make</em>.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:2},
            {w:"are", pos:"AUX", func:"aux", head:2},
            {w:"doing", pos:"VERB", func:"verb", head:-1},
            {w:"research", pos:"NOUN", func:"dObj", head:2},
            {w:"for", pos:"ADP", func:"adjunct", head:2},
            {w:"their", pos:"DET", func:"det", head:7},
            {w:"final", pos:"ADJ", func:"mod", head:7},
            {w:"project", pos:"NOUN", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V IO DO", en:"Can you please do me a favor?",
          vi:"Bạn giúp tôi một việc được không?",
          note:"Đúng khung hai tân ngữ của câu chính, lần này ở dạng câu hỏi. <strong>Can</strong> là trợ động từ nên nhảy lên trước chủ ngữ, còn trật tự <em>me – a favor</em> thì giữ nguyên.",
          tokens:[
            {w:"Can", pos:"AUX", func:"aux", head:3},
            {w:"you", pos:"PRON", func:"subj", head:3},
            {w:"please", pos:"ADV", func:"adjunct", head:3},
            {w:"do", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"iObj", head:3},
            {w:"a", pos:"DET", func:"det", head:6},
            {w:"favor", pos:"NOUN", func:"dObj", head:3}
          ]
        },
        {
          pattern:"S V O + mục đích", en:"He does exercise every day to stay fit.",
          vi:"Anh ấy tập thể dục mỗi ngày để giữ dáng.",
          note:"<strong>to stay fit</strong> nói <em>tập để làm gì</em>, nên là bổ nghĩa mục đích chứ không phải tân ngữ thứ hai. Bên trong nó, <em>fit</em> lại là bổ ngữ chủ ngữ của <em>stay</em>.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"does", pos:"VERB", func:"verb", head:-1},
            {w:"exercise", pos:"NOUN", func:"dObj", head:1},
            {w:"every", pos:"DET", func:"det", head:4},
            {w:"day", pos:"NOUN", func:"adjunct", head:1},
            {w:"to", pos:"PART", func:"to", head:6},
            {w:"stay", pos:"VERB", func:"adjunct", head:1},
            {w:"fit", pos:"ADJ", func:"sComp", head:6}
          ]
        },
        {
          pattern:"S V O + mục đích", en:"She did her best to comfort her friend during a difficult time.",
          vi:"Cô ấy đã cố hết sức để an ủi bạn mình trong lúc khó khăn.",
          note:"Thành ngữ <em>do one’s best</em> nằm gọn trong khung SVO: <strong>her best</strong> là tân ngữ thật. Phần còn lại là một chuỗi bổ nghĩa lồng nhau — mục đích <em>to comfort…</em>, rồi trong đó là thời gian <em>during…</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"did", pos:"VERB", func:"verb", head:-1},
            {w:"her", pos:"DET", func:"det", head:3},
            {w:"best", pos:"NOUN", func:"dObj", head:1},
            {w:"to", pos:"PART", func:"to", head:5},
            {w:"comfort", pos:"VERB", func:"adjunct", head:1},
            {w:"her", pos:"DET", func:"det", head:7},
            {w:"friend", pos:"NOUN", func:"dObj", head:5},
            {w:"during", pos:"ADP", func:"adjunct", head:5},
            {w:"a", pos:"DET", func:"det", head:11},
            {w:"difficult", pos:"ADJ", func:"mod", head:11},
            {w:"time", pos:"NOUN", func:"pComp", head:8}
          ]
        },
        {
          pattern:"V O + mệnh đề (mệnh lệnh)", en:"Please do this task before the meeting starts.",
          vi:"Làm ơn làm việc này trước khi cuộc họp bắt đầu.",
          note:"Câu mệnh lệnh nên không có nút chủ ngữ. <strong>before…</strong> kéo theo cả một mệnh đề có chủ ngữ riêng — <em>the meeting</em> là chủ ngữ của <em>starts</em>, không phải của <em>do</em>.",
          tokens:[
            {w:"Please", pos:"ADV", func:"adjunct", head:1},
            {w:"do", pos:"VERB", func:"verb", head:-1},
            {w:"this", pos:"DET", func:"det", head:3},
            {w:"task", pos:"NOUN", func:"dObj", head:1},
            {w:"before", pos:"ADP", func:"adjunct", head:1},
            {w:"the", pos:"DET", func:"det", head:6},
            {w:"meeting", pos:"NOUN", func:"subj", head:7},
            {w:"starts", pos:"VERB", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V O + mệnh đề", en:"I will do the dishes after we finish dinner.",
          vi:"Tôi sẽ rửa bát sau khi chúng ta ăn xong.",
          note:"<em>do the dishes</em> = rửa bát, không phải “làm những cái đĩa”. Mệnh đề <strong>after we finish dinner</strong> có chủ ngữ và tân ngữ riêng, treo vào động từ chính như một trạng ngữ.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:2},
            {w:"will", pos:"AUX", func:"aux", head:2},
            {w:"do", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"dishes", pos:"NOUN", func:"dObj", head:2},
            {w:"after", pos:"ADP", func:"adjunct", head:2},
            {w:"we", pos:"PRON", func:"subj", head:7},
            {w:"finish", pos:"VERB", func:"pComp", head:5},
            {w:"dinner", pos:"NOUN", func:"dObj", head:7}
          ]
        }
      ]
    },
    {
      verb:"get", pattern:"S V O C", en:"I got him to fix my car.",
      vi:"Tôi nhờ anh ấy sửa xe.",
      note:"Cấu trúc sai khiến trong ghi chú <em>Get</em>: <em>Get + someone + TO do something</em>. <strong>him</strong> là tân ngữ, còn <strong>to fix my car</strong> là bổ ngữ nói về <em>him</em> — người sửa xe là anh ấy, không phải tôi.",
      tokens:[
        {w:"I", pos:"PRON", func:"subj", head:1},
        {w:"got", pos:"VERB", func:"verb", head:-1},
        {w:"him", pos:"PRON", func:"dObj", head:1},
        {w:"to", pos:"PART", func:"to", head:4},
        {w:"fix", pos:"VERB", func:"oComp", head:1},
        {w:"my", pos:"DET", func:"det", head:6},
        {w:"car", pos:"NOUN", func:"dObj", head:4}
      ],
      ex:[
        {
          pattern:"S V C", en:"It is getting cold.",
          vi:"Trời đang lạnh dần.",
          note:"<em>get</em> = <em>become</em>. <strong>cold</strong> là tính từ nói về <em>It</em> — bổ ngữ chủ ngữ, y như <em>tired</em> trong <em>You look tired</em>. Ở khung này <em>get</em> không có tân ngữ nào.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:2},
            {w:"is", pos:"AUX", func:"aux", head:2},
            {w:"getting", pos:"VERB", func:"verb", head:-1},
            {w:"cold", pos:"ADJ", func:"sComp", head:2}
          ]
        },
        {
          pattern:"bị động với get", en:"He got fired yesterday.",
          vi:"Anh ấy bị đuổi việc hôm qua.",
          note:"Lối nói đời thường của thể bị động: <em>get</em> + V3 đứng thay <em>be</em> + V3. So với <em>The letter was written</em> — cùng ý bị động, nhưng <em>got</em> nghe đời hơn và thường kèm chuyện không hay.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"got", pos:"VERB", func:"verb", head:-1},
            {w:"fired", pos:"VERB", func:"sComp", head:1},
            {w:"yesterday", pos:"ADV", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V O A", en:"I got a letter from her yesterday.",
          vi:"Hôm qua tôi nhận được thư của cô ấy.",
          note:"<em>get</em> = <em>receive</em>. Khung SVO thường, cộng hai trạng ngữ tháo được: <strong>from her</strong> và <strong>yesterday</strong>.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:1},
            {w:"got", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"letter", pos:"NOUN", func:"dObj", head:1},
            {w:"from", pos:"ADP", func:"adjunct", head:1},
            {w:"her", pos:"PRON", func:"pComp", head:4},
            {w:"yesterday", pos:"ADV", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V A*", en:"She got home very late.",
          vi:"Cô ấy về đến nhà rất muộn.",
          note:"<em>get</em> = <em>arrive</em>. Đáng nhớ: <strong>home</strong> không có <em>to</em> đứng trước — giống <em>go home</em>. Nó là trạng từ chỉ nơi chốn, không phải danh từ sau giới từ.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"got", pos:"VERB", func:"verb", head:-1},
            {w:"home", pos:"ADV", func:"aComp", head:1},
            {w:"very", pos:"ADV", func:"mod", head:4},
            {w:"late", pos:"ADV", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"V IO DO (mệnh lệnh)", en:"Please get me a chair.",
          vi:"Làm ơn lấy cho tôi một cái ghế.",
          note:"<em>get</em> = <em>fetch</em>, đi lấy về. Ở nghĩa này nó mở ra khung <strong>hai tân ngữ</strong> — cùng khuôn với <em>give me a book</em>.",
          tokens:[
            {w:"Please", pos:"ADV", func:"adjunct", head:1},
            {w:"get", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"iObj", head:1},
            {w:"a", pos:"DET", func:"det", head:4},
            {w:"chair", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V O", en:"I don’t get the joke.",
          vi:"Tôi không hiểu câu đùa đó.",
          note:"<em>get</em> = <em>understand</em>. Vẫn là khung SVO bình thường — nghĩa đổi hẳn nhưng hình dạng câu thì không.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:2},
            {w:"don’t", pos:"AUX", func:"aux", head:2},
            {w:"get", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"joke", pos:"NOUN", func:"dObj", head:2}
          ]
        },
        {
          pattern:"S V prt prt O", en:"It is time to get rid of these old shoes.",
          vi:"Đến lúc vứt đôi giày cũ này rồi.",
          note:"Phrasal verb <strong>ba phần</strong>: <em>get rid of</em> dính liền thành một khối, không tách chen tân ngữ vào giữa được. So với <em>turn the light off</em> tách được — đó là khác biệt lớn nhất giữa các loại phrasal verb.",
          tokens:[
            {w:"It", pos:"PRON", func:"expl", head:1},
            {w:"is", pos:"VERB", func:"verb", head:-1},
            {w:"time", pos:"NOUN", func:"sComp", head:1},
            {w:"to", pos:"PART", func:"to", head:4},
            {w:"get", pos:"VERB", func:"cComp", head:1},
            {w:"rid", pos:"ADJ", func:"oComp", head:4},
            {w:"of", pos:"ADP", func:"aComp", head:4},
            {w:"these", pos:"DET", func:"det", head:9},
            {w:"old", pos:"ADJ", func:"mod", head:9},
            {w:"shoes", pos:"NOUN", func:"pComp", head:6}
          ]
        }
      ]
    },
    {
      verb:"go", pattern:"S V A*", en:"The children go to school.",
      vi:"Bọn trẻ đi học.",
      note:"<em>go</em> là nội động từ: <strong>không có tân ngữ nào cả</strong>. <em>to school</em> là bổ ngữ chỉ hướng — cần cho câu trọn nghĩa nhưng không phải tân ngữ. Ghi chú <em>Go</em> cũng lưu ý: <em>go home</em> thì không có “to”.",
      tokens:[
        {w:"The", pos:"DET", func:"det", head:1},
        {w:"children", pos:"NOUN", func:"subj", head:2},
        {w:"go", pos:"VERB", func:"verb", head:-1},
        {w:"to", pos:"ADP", func:"aComp", head:2},
        {w:"school", pos:"NOUN", func:"pComp", head:3}
      ],
      ex:[
        {
          pattern:"S V C", en:"The milk went sour.",
          vi:"Sữa bị chua.",
          note:"Nghĩa “đổi trạng thái, thường xấu đi” trong ghi chú <em>Go</em>. Ở đây <em>go</em> thành <strong>động từ nối</strong>: không đi đâu cả, chỉ bắc cầu sang tính từ <strong>sour</strong>. Cùng họ với <em>look tired</em>, <em>get cold</em>.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"milk", pos:"NOUN", func:"subj", head:2},
            {w:"went", pos:"VERB", func:"verb", head:-1},
            {w:"sour", pos:"ADJ", func:"sComp", head:2}
          ]
        },
        {
          pattern:"S V + bổ nghĩa", en:"We went swimming.",
          vi:"Chúng tôi đi bơi.",
          note:"<em>Go + V-ing</em> cho hoạt động giải trí. Phép thử bỏ đi: <em>We went</em> vẫn là câu đúng, nên <strong>swimming</strong> chỉ là bổ nghĩa tùy ý — khác <em>to school</em> ở câu chính, bỏ đi thì câu cụt.",
          tokens:[
            {w:"We", pos:"PRON", func:"subj", head:1},
            {w:"went", pos:"VERB", func:"verb", head:-1},
            {w:"swimming", pos:"VERB", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V A A", en:"He goes to work by train.",
          vi:"Anh ấy đi làm bằng tàu.",
          note:"Hai giới ngữ, hai vai khác nhau: <strong>to work</strong> là nơi đến — bỏ đi thì câu cụt; <strong>by train</strong> là phương tiện — bỏ đi <em>He goes to work</em> vẫn đúng. Cùng dạng giới ngữ mà một cái bắt buộc, một cái tùy ý.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"goes", pos:"VERB", func:"verb", head:-1},
            {w:"to", pos:"ADP", func:"aComp", head:1},
            {w:"work", pos:"NOUN", func:"pComp", head:2},
            {w:"by", pos:"ADP", func:"adjunct", head:1},
            {w:"train", pos:"NOUN", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V + bổ nghĩa", en:"She went shopping yesterday afternoon.",
          vi:"Chiều hôm qua cô ấy đi mua sắm.",
          note:"<em>Go + V-ing</em> lần nữa, lần này kèm mốc thời gian. Cả <strong>shopping</strong> lẫn <strong>yesterday afternoon</strong> đều tháo ra được — <em>She went</em> vẫn là câu đúng.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"went", pos:"VERB", func:"verb", head:-1},
            {w:"shopping", pos:"VERB", func:"adjunct", head:1},
            {w:"yesterday", pos:"ADJ", func:"mod", head:4},
            {w:"afternoon", pos:"NOUN", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V + bổ nghĩa", en:"They go jogging every morning.",
          vi:"Sáng nào họ cũng đi chạy bộ.",
          note:"<em>go jogging</em>, <em>go swimming</em>, <em>go shopping</em> — cùng một khuôn. Đáng nhớ: sau <em>go</em> là V-ing chứ không phải <em>to</em> + động từ nguyên thể.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:1},
            {w:"go", pos:"VERB", func:"verb", head:-1},
            {w:"jogging", pos:"VERB", func:"adjunct", head:1},
            {w:"every", pos:"DET", func:"det", head:4},
            {w:"morning", pos:"NOUN", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V A*", en:"This tie goes with your shirt.",
          vi:"Cà vạt này hợp với áo sơ mi của bạn.",
          note:"Nghĩa “hợp nhau”, không còn dính gì tới di chuyển. Chủ ngữ là vật, và <strong>with your shirt</strong> bỏ đi thì câu mất nghĩa — nên là bổ ngữ bắt buộc.",
          tokens:[
            {w:"This", pos:"DET", func:"det", head:1},
            {w:"tie", pos:"NOUN", func:"subj", head:2},
            {w:"goes", pos:"VERB", func:"verb", head:-1},
            {w:"with", pos:"ADP", func:"aComp", head:2},
            {w:"your", pos:"DET", func:"det", head:5},
            {w:"shirt", pos:"NOUN", func:"pComp", head:3}
          ]
        }
      ]
    },
    {
      verb:"have", pattern:"S V O", en:"They have two children.",
      vi:"Họ có hai đứa con.",
      note:"Khung SVO gọn nhất. <strong>children</strong> là tân ngữ — bỏ đi câu sụp. <strong>two</strong> chỉ là bổ nghĩa — bỏ đi câu vẫn đúng. Hai thứ khác hẳn nhau tuy đứng cạnh nhau.",
      tokens:[
        {w:"They", pos:"PRON", func:"subj", head:1},
        {w:"have", pos:"VERB", func:"verb", head:-1},
        {w:"two", pos:"NUM", func:"mod", head:3},
        {w:"children", pos:"NOUN", func:"dObj", head:1}
      ],
      ex:[
        {
          pattern:"trợ động từ", en:"I have finished.",
          vi:"Tôi làm xong rồi.",
          note:"<em>have</em> ở đây <strong>không nghĩa là “có”</strong> — nó là trợ động từ của thì hoàn thành. Gốc câu nằm ở <strong>finished</strong>. Đây là chỗ khác nhau giữa <em>have</em> động từ chính và <em>have</em> trợ động từ.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:2},
            {w:"have", pos:"AUX", func:"aux", head:2},
            {w:"finished", pos:"VERB", func:"verb", head:-1}
          ]
        },
        {
          pattern:"S V O C", en:"I had my car washed.",
          vi:"Tôi mang xe đi rửa.",
          note:"<em>have something done</em>: người rửa xe <strong>không phải tôi</strong>. <strong>washed</strong> nói về <em>car</em> nên là bổ ngữ tân ngữ — cùng chỗ đứng với <em>happy</em> trong <em>You make me happy</em>.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:1},
            {w:"had", pos:"VERB", func:"verb", head:-1},
            {w:"my", pos:"DET", func:"det", head:3},
            {w:"car", pos:"NOUN", func:"dObj", head:1},
            {w:"washed", pos:"VERB", func:"oComp", head:1}
          ]
        },
        {
          pattern:"S V O A", en:"They had dinner at eight.",
          vi:"Họ ăn tối lúc tám giờ.",
          note:"Nghĩa ăn uống. Tiếng Anh nói <em>have dinner</em> chứ không <em>eat dinner</em> — <em>have</em> lại là động từ rỗng nghĩa, chỗ chứa nghĩa nằm ở danh từ.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:1},
            {w:"had", pos:"VERB", func:"verb", head:-1},
            {w:"dinner", pos:"NOUN", func:"dObj", head:1},
            {w:"at", pos:"ADP", func:"adjunct", head:1},
            {w:"eight", pos:"NUM", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V O", en:"I have a terrible headache.",
          vi:"Tôi đau đầu kinh khủng.",
          note:"Nghĩa sức khỏe. Tiếng Việt dùng tính từ “đau”, tiếng Anh lại dùng <em>have</em> + danh từ — nên <strong>headache</strong> đứng ở ô tân ngữ, không phải ô bổ ngữ.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:1},
            {w:"have", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:4},
            {w:"terrible", pos:"ADJ", func:"mod", head:4},
            {w:"headache", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"V O A (mệnh lệnh)", en:"Have a look at this.",
          vi:"Ngó qua cái này xem.",
          note:"<em>have a look</em> thật ra chỉ là <em>look</em>. Nhưng khung câu thì đổi hẳn: nghĩa dồn vào danh từ <strong>a look</strong>, còn thứ được nhìn phải đi qua giới từ <em>at</em>.",
          tokens:[
            {w:"Have", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:2},
            {w:"look", pos:"NOUN", func:"dObj", head:0},
            {w:"at", pos:"ADP", func:"aComp", head:0},
            {w:"this", pos:"PRON", func:"pComp", head:3}
          ]
        }
      ]
    },
    {
      verb:"keep", pattern:"S V C(V-ing)", en:"He keeps talking.",
      vi:"Anh ấy cứ nói mãi.",
      note:"Ghi chú <em>Keep</em>: <em>Keep + V-ing</em> cho hành động tiếp diễn. <strong>talking</strong> không phải tân ngữ — nó không bị tác động, mà là bổ ngữ cho biết <em>keep</em> đang duy trì cái gì.",
      tokens:[
        {w:"He", pos:"PRON", func:"subj", head:1},
        {w:"keeps", pos:"VERB", func:"verb", head:-1},
        {w:"talking", pos:"VERB", func:"cComp", head:1}
      ],
      ex:[
        {
          pattern:"V O C (mệnh lệnh)", en:"Keep the door open.",
          vi:"Cứ để cửa mở.",
          note:"Câu mệnh lệnh nên <strong>chủ ngữ ẩn</strong> — đồ thị không có nút chủ ngữ nào, và đó là chuyện bình thường. <strong>open</strong> nói về <em>the door</em>, nên là bổ ngữ tân ngữ chứ không phải bổ ngữ chủ ngữ.",
          tokens:[
            {w:"Keep", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:2},
            {w:"door", pos:"NOUN", func:"dObj", head:0},
            {w:"open", pos:"ADJ", func:"oComp", head:0}
          ]
        },
        {
          pattern:"S V O A", en:"The noise kept me from sleeping.",
          vi:"Tiếng ồn làm tôi không ngủ được.",
          note:"<em>keep + ai + from V-ing</em> = ngăn không cho làm. <strong>from sleeping</strong> bỏ đi thì <em>The noise kept me</em> đổi nghĩa hẳn, nên nó là bổ ngữ bắt buộc. Bên trong, <em>sleeping</em> là bổ ngữ của giới từ <em>from</em>.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"noise", pos:"NOUN", func:"subj", head:2},
            {w:"kept", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"dObj", head:2},
            {w:"from", pos:"ADP", func:"aComp", head:2},
            {w:"sleeping", pos:"VERB", func:"pComp", head:4}
          ]
        },
        {
          pattern:"V O C + mục đích", en:"Please keep the door closed to maintain the privacy of the room.",
          vi:"Làm ơn đóng cửa để giữ sự riêng tư cho căn phòng.",
          note:"Cùng khung <em>keep + O + tính từ</em> như <em>Keep the door open</em>, nhưng nối thêm một mệnh đề mục đích. <strong>to maintain…</strong> bỏ đi thì câu vẫn đúng, nên nó là bổ nghĩa tùy ý — bật “Chỉ khung câu” là thấy nó rụng, còn <strong>closed</strong> thì ở lại.",
          tokens:[
            {w:"Please", pos:"ADV", func:"adjunct", head:1},
            {w:"keep", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:3},
            {w:"door", pos:"NOUN", func:"dObj", head:1},
            {w:"closed", pos:"ADJ", func:"oComp", head:1},
            {w:"to", pos:"PART", func:"to", head:6},
            {w:"maintain", pos:"VERB", func:"adjunct", head:1},
            {w:"the", pos:"DET", func:"det", head:8},
            {w:"privacy", pos:"NOUN", func:"dObj", head:6},
            {w:"of", pos:"ADP", func:"mod", head:8},
            {w:"the", pos:"DET", func:"det", head:11},
            {w:"room", pos:"NOUN", func:"pComp", head:9}
          ]
        },
        {
          pattern:"S V O A + mệnh đề", en:"Can you keep an eye on my bag while I go to the restroom?",
          vi:"Bạn trông giúp cái túi của tôi trong lúc tôi đi vệ sinh nhé?",
          note:"Thành ngữ <em>keep an eye on</em> nằm nguyên trong khung: <strong>an eye</strong> là tân ngữ, <strong>on my bag</strong> là bổ ngữ bắt buộc — thiếu nó thì <em>keep an eye</em> không thành câu. Mệnh đề <strong>while…</strong> treo vào động từ chính và bỏ được.",
          tokens:[
            {w:"Can", pos:"AUX", func:"aux", head:2},
            {w:"you", pos:"PRON", func:"subj", head:2},
            {w:"keep", pos:"VERB", func:"verb", head:-1},
            {w:"an", pos:"DET", func:"det", head:4},
            {w:"eye", pos:"NOUN", func:"dObj", head:2},
            {w:"on", pos:"ADP", func:"aComp", head:2},
            {w:"my", pos:"DET", func:"det", head:7},
            {w:"bag", pos:"NOUN", func:"pComp", head:5},
            {w:"while", pos:"ADP", func:"adjunct", head:2},
            {w:"I", pos:"PRON", func:"subj", head:10},
            {w:"go", pos:"VERB", func:"pComp", head:8},
            {w:"to", pos:"ADP", func:"aComp", head:10},
            {w:"the", pos:"DET", func:"det", head:13},
            {w:"restroom", pos:"NOUN", func:"pComp", head:11}
          ]
        },
        {
          pattern:"S V O", en:"He can’t keep a secret.",
          vi:"Anh ấy không giữ được bí mật.",
          note:"Nghĩa “lưu giữ”, khung SVO trần. Ở đây <strong>a secret</strong> là tân ngữ thật — khác hẳn <em>keep talking</em>, nơi thứ đứng sau là bổ ngữ chứ không bị tác động.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:2},
            {w:"can’t", pos:"AUX", func:"aux", head:2},
            {w:"keep", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:4},
            {w:"secret", pos:"NOUN", func:"dObj", head:2}
          ]
        }
      ]
    },
    {
      verb:"look", pattern:"S V C", en:"You look tired.",
      vi:"Trông bạn có vẻ mệt.",
      note:"Ghi chú <em>Look</em>: <em>Look + Adjective</em>. <strong>tired</strong> là tính từ làm bổ ngữ chủ ngữ — nó nói về <em>You</em>. Vì thế không nói được <em>You look tiredly</em>.",
      tokens:[
        {w:"You", pos:"PRON", func:"subj", head:1},
        {w:"look", pos:"VERB", func:"verb", head:-1},
        {w:"tired", pos:"ADJ", func:"sComp", head:1}
      ],
      ex:[
        {
          pattern:"V A (mệnh lệnh)", en:"Look at the board.",
          vi:"Nhìn lên bảng đi.",
          note:"Nghĩa “nhìn bằng mắt” thì <strong>bắt buộc có <em>at</em></strong> — <em>look the board</em> là câu sai. Đây là chỗ <em>look</em> khác <em>see</em> và <em>watch</em>, hai từ kia lấy thẳng tân ngữ không cần giới từ.",
          tokens:[
            {w:"Look", pos:"VERB", func:"verb", head:-1},
            {w:"at", pos:"ADP", func:"aComp", head:0},
            {w:"the", pos:"DET", func:"det", head:3},
            {w:"board", pos:"NOUN", func:"pComp", head:1}
          ]
        },
        {
          pattern:"S V C", en:"He looks like his father.",
          vi:"Anh ấy giống bố.",
          note:"<em>look like</em> + danh từ. <strong>like</strong> ở đây là <strong>giới từ</strong>, không phải động từ “thích” — nó dẫn ra cả cụm làm bổ ngữ chủ ngữ. Cùng ô với <em>tired</em> ở câu chính, chỉ khác dạng từ.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"looks", pos:"VERB", func:"verb", head:-1},
            {w:"like", pos:"ADP", func:"sComp", head:1},
            {w:"his", pos:"DET", func:"det", head:4},
            {w:"father", pos:"NOUN", func:"pComp", head:2}
          ]
        },
        {
          pattern:"S V C(mệnh đề)", en:"It looks as if they have left.",
          vi:"Có vẻ như họ đã đi rồi.",
          note:"<strong>It</strong> là chủ ngữ giả — không trỏ vào vật gì. Bổ ngữ của <em>look</em> lần này là cả một mệnh đề có chủ ngữ riêng, thay vì một tính từ như <em>You look tired</em>.",
          tokens:[
            {w:"It", pos:"PRON", func:"expl", head:1},
            {w:"looks", pos:"VERB", func:"verb", head:-1},
            {w:"as", pos:"ADV", func:"mod", head:3},
            {w:"if", pos:"ADP", func:"sComp", head:1},
            {w:"they", pos:"PRON", func:"subj", head:6},
            {w:"have", pos:"AUX", func:"aux", head:6},
            {w:"left", pos:"VERB", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V A*", en:"The window looks onto the garden.",
          vi:"Cửa sổ nhìn ra vườn.",
          note:"Nghĩa “hướng ra” — chủ ngữ không phải người mà là một vật đứng yên. Vẫn cần giới từ, lần này là <em>onto</em> chứ không phải <em>at</em>.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"window", pos:"NOUN", func:"subj", head:2},
            {w:"looks", pos:"VERB", func:"verb", head:-1},
            {w:"onto", pos:"ADP", func:"aComp", head:2},
            {w:"the", pos:"DET", func:"det", head:5},
            {w:"garden", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"V prt A (mệnh lệnh)", en:"Look out for the car.",
          vi:"Coi chừng cái xe.",
          note:"Hai từ nhỏ, hai vai khác nhau: <strong>out</strong> là tiểu từ dính vào <em>look</em> để thành nghĩa “coi chừng”, còn <strong>for</strong> mới là giới từ thật, dẫn ra thứ phải coi chừng.",
          tokens:[
            {w:"Look", pos:"VERB", func:"verb", head:-1},
            {w:"out", pos:"ADV", func:"prt", head:0},
            {w:"for", pos:"ADP", func:"aComp", head:0},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"car", pos:"NOUN", func:"pComp", head:2}
          ]
        },
        {
          pattern:"S V prt O (không tách)", en:"Could you look after my cat this weekend?",
          vi:"Cuối tuần này bạn trông hộ con mèo của tôi nhé?",
          note:"<em>look after</em> <strong>không tách được</strong>: không nói <em>*look my cat after</em>. Vì <em>after</em> là giới từ thật, nó phải dính với danh từ của mình. Đặt cạnh <em>put the meeting off</em> — cùng trông như phrasal verb, nhưng một cái tách được, một cái không.",
          tokens:[
            {w:"Could", pos:"AUX", func:"aux", head:2},
            {w:"you", pos:"PRON", func:"subj", head:2},
            {w:"look", pos:"VERB", func:"verb", head:-1},
            {w:"after", pos:"ADP", func:"aComp", head:2},
            {w:"my", pos:"DET", func:"det", head:5},
            {w:"cat", pos:"NOUN", func:"pComp", head:3},
            {w:"this", pos:"DET", func:"det", head:7},
            {w:"weekend", pos:"NOUN", func:"adjunct", head:2}
          ]
        }
      ]
    },
    {
      verb:"make", pattern:"S V O C", en:"You make me happy.",
      vi:"Bạn làm tôi hạnh phúc.",
      note:"Ghi chú <em>Make</em>: <em>Make + someone + Adjective</em>. <strong>happy</strong> nói về <em>me</em>, không phải về <em>You</em> — nên nó là bổ ngữ tân ngữ. So với <em>You look tired</em>: cùng là tính từ, khác chức vụ.",
      tokens:[
        {w:"You", pos:"PRON", func:"subj", head:1},
        {w:"make", pos:"VERB", func:"verb", head:-1},
        {w:"me", pos:"PRON", func:"dObj", head:1},
        {w:"happy", pos:"ADJ", func:"oComp", head:1}
      ],
      ex:[
        {
          pattern:"S V O C", en:"He made me wait.",
          vi:"Anh ta bắt tôi chờ.",
          note:"<em>make + ai + V</em> — động từ nguyên thể <strong>không có “to”</strong>. Đặt cạnh <em>I got him to fix my car</em>: cùng ý sai khiến, nhưng <em>get</em> đòi <em>to</em> còn <em>make</em> thì không.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"made", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"dObj", head:1},
            {w:"wait", pos:"VERB", func:"oComp", head:1}
          ]
        },
        {
          pattern:"bị động", en:"I was made to wait.",
          vi:"Tôi bị bắt phải chờ.",
          note:"Chính câu trên lật sang bị động — và <strong>“to” quay trở lại</strong>. Đây là bẫy quen thuộc: chủ động <em>made me wait</em>, bị động <em>was made to wait</em>. Tân ngữ cũ <em>me</em> giờ lên làm chủ ngữ <em>I</em>.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:2},
            {w:"was", pos:"AUX", func:"aux", head:2},
            {w:"made", pos:"VERB", func:"verb", head:-1},
            {w:"to", pos:"PART", func:"to", head:4},
            {w:"wait", pos:"VERB", func:"cComp", head:2}
          ]
        },
        {
          pattern:"S V O C", en:"His jokes make everyone laugh.",
          vi:"Mấy câu đùa của anh ấy làm ai cũng bật cười.",
          note:"<em>make + ai + V nguyên thể</em> lần nữa, nhưng ở nghĩa “khiến” chứ không phải “ép”. <strong>laugh</strong> không có <em>to</em> đứng trước — dấu hiệu nhận ra khung này.",
          tokens:[
            {w:"His", pos:"DET", func:"det", head:1},
            {w:"jokes", pos:"NOUN", func:"subj", head:2},
            {w:"make", pos:"VERB", func:"verb", head:-1},
            {w:"everyone", pos:"PRON", func:"dObj", head:2},
            {w:"laugh", pos:"VERB", func:"oComp", head:2}
          ]
        },
        {
          pattern:"S V O C", en:"Public speaking makes me nervous.",
          vi:"Nói trước đám đông làm tôi căng thẳng.",
          note:"Cùng khung với <em>You make me happy</em>: <strong>nervous</strong> nói về <em>me</em>, không nói về chủ ngữ. Chủ ngữ ở đây là cả một cụm danh từ <strong>Public speaking</strong>.",
          tokens:[
            {w:"Public", pos:"ADJ", func:"mod", head:1},
            {w:"speaking", pos:"NOUN", func:"subj", head:2},
            {w:"makes", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"dObj", head:2},
            {w:"nervous", pos:"ADJ", func:"oComp", head:2}
          ]
        },
        {
          pattern:"S V O C", en:"Your support makes me feel confident.",
          vi:"Sự ủng hộ của bạn làm tôi thấy tự tin.",
          note:"Bổ ngữ tân ngữ lần này là cả một động từ: <strong>feel</strong> nói về <em>me</em>. Mà bên trong nó lại có bổ ngữ riêng — <em>confident</em> nói về chủ ngữ của <em>feel</em>. Hai tầng bổ ngữ lồng nhau.",
          tokens:[
            {w:"Your", pos:"DET", func:"det", head:1},
            {w:"support", pos:"NOUN", func:"subj", head:2},
            {w:"makes", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"dObj", head:2},
            {w:"feel", pos:"VERB", func:"oComp", head:2},
            {w:"confident", pos:"ADJ", func:"sComp", head:4}
          ]
        },
        {
          pattern:"S V O A", en:"I make breakfast for my family every morning.",
          vi:"Sáng nào tôi cũng nấu bữa sáng cho cả nhà.",
          note:"Nghĩa gốc “tạo ra”. <strong>for my family</strong> và <strong>every morning</strong> đều tháo ra được — xương chỉ còn <em>I make breakfast</em>.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:1},
            {w:"make", pos:"VERB", func:"verb", head:-1},
            {w:"breakfast", pos:"NOUN", func:"dObj", head:1},
            {w:"for", pos:"ADP", func:"adjunct", head:1},
            {w:"my", pos:"DET", func:"det", head:5},
            {w:"family", pos:"NOUN", func:"pComp", head:3},
            {w:"every", pos:"DET", func:"det", head:7},
            {w:"morning", pos:"NOUN", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V O A", en:"She made a handmade gift for her best friend.",
          vi:"Cô ấy làm một món quà thủ công tặng bạn thân.",
          note:"Chuỗi bổ nghĩa xếp chồng: <em>handmade</em> bổ nghĩa cho <em>gift</em>, <em>best</em> bổ nghĩa cho <em>friend</em>. Tháo hết vẫn còn <em>She made a gift</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"made", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:4},
            {w:"handmade", pos:"ADJ", func:"mod", head:4},
            {w:"gift", pos:"NOUN", func:"dObj", head:1},
            {w:"for", pos:"ADP", func:"adjunct", head:1},
            {w:"her", pos:"DET", func:"det", head:8},
            {w:"best", pos:"ADJ", func:"mod", head:8},
            {w:"friend", pos:"NOUN", func:"pComp", head:5}
          ]
        },
        {
          pattern:"S V O A", en:"The manager made an announcement this morning.",
          vi:"Sáng nay quản lý ra thông báo.",
          note:"<em>make an announcement</em> — rỗng nghĩa, đứng thay cho <em>announce</em>. Chỉ có <strong>một</strong> tân ngữ: <em>this morning</em> là trạng ngữ thời gian, không phải tân ngữ thứ hai.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"manager", pos:"NOUN", func:"subj", head:2},
            {w:"made", pos:"VERB", func:"verb", head:-1},
            {w:"an", pos:"DET", func:"det", head:4},
            {w:"announcement", pos:"NOUN", func:"dObj", head:2},
            {w:"this", pos:"DET", func:"det", head:6},
            {w:"morning", pos:"NOUN", func:"adjunct", head:2}
          ]
        },
        {
          pattern:"S V O A", en:"She is making good progress in her studies.",
          vi:"Cô ấy đang tiến bộ tốt trong việc học.",
          note:"<em>make progress</em> — lại là <em>make</em> rỗng nghĩa, đứng thay cho <em>progress</em> ở vai động từ. <strong>is</strong> chỉ là trợ động từ của thì tiếp diễn.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:2},
            {w:"is", pos:"AUX", func:"aux", head:2},
            {w:"making", pos:"VERB", func:"verb", head:-1},
            {w:"good", pos:"ADJ", func:"mod", head:4},
            {w:"progress", pos:"NOUN", func:"dObj", head:2},
            {w:"in", pos:"ADP", func:"adjunct", head:2},
            {w:"her", pos:"DET", func:"det", head:7},
            {w:"studies", pos:"NOUN", func:"pComp", head:5}
          ]
        },
        {
          pattern:"S V O + bổ nghĩa", en:"He made a promise to help me.",
          vi:"Anh ấy hứa sẽ giúp tôi.",
          note:"<strong>to help me</strong> nói rõ lời hứa là gì, nên nó bổ nghĩa cho <em>a promise</em> chứ không treo vào động từ. Đây là chỗ khác với <em>to</em> chỉ mục đích — so với <em>She ran to catch the bus</em>.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"made", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"promise", pos:"NOUN", func:"dObj", head:1},
            {w:"to", pos:"PART", func:"to", head:5},
            {w:"help", pos:"VERB", func:"mod", head:3},
            {w:"me", pos:"PRON", func:"dObj", head:5}
          ]
        },
        {
          pattern:"S V O + mục đích", en:"You should make more effort to improve your English.",
          vi:"Bạn nên cố gắng nhiều hơn để cải thiện tiếng Anh.",
          note:"<em>make an effort</em> — rỗng nghĩa nữa. Lần này <strong>to improve…</strong> treo vào động từ chính, nói <em>cố gắng để làm gì</em>, nên là mục đích chứ không bổ nghĩa cho <em>effort</em>.",
          tokens:[
            {w:"You", pos:"PRON", func:"subj", head:2},
            {w:"should", pos:"AUX", func:"aux", head:2},
            {w:"make", pos:"VERB", func:"verb", head:-1},
            {w:"more", pos:"ADJ", func:"mod", head:4},
            {w:"effort", pos:"NOUN", func:"dObj", head:2},
            {w:"to", pos:"PART", func:"to", head:6},
            {w:"improve", pos:"VERB", func:"adjunct", head:2},
            {w:"your", pos:"DET", func:"det", head:8},
            {w:"English", pos:"PROPN", func:"dObj", head:6}
          ]
        },
        {
          pattern:"S V O + bổ nghĩa", en:"She can make beautiful artwork using various painting techniques.",
          vi:"Cô ấy có thể tạo ra tranh đẹp bằng nhiều kỹ thuật vẽ khác nhau.",
          note:"<strong>using…</strong> là phân từ chỉ cách thức, treo vào động từ chính và bỏ được. Nó có tân ngữ riêng — <em>techniques</em> thuộc về <em>using</em>, không thuộc về <em>make</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:2},
            {w:"can", pos:"AUX", func:"aux", head:2},
            {w:"make", pos:"VERB", func:"verb", head:-1},
            {w:"beautiful", pos:"ADJ", func:"mod", head:4},
            {w:"artwork", pos:"NOUN", func:"dObj", head:2},
            {w:"using", pos:"VERB", func:"adjunct", head:2},
            {w:"various", pos:"ADJ", func:"mod", head:8},
            {w:"painting", pos:"NOUN", func:"mod", head:8},
            {w:"techniques", pos:"NOUN", func:"dObj", head:5}
          ]
        },
        {
          pattern:"S V O A + mệnh đề", en:"My mother made a dress for me when I was a child.",
          vi:"Hồi tôi còn nhỏ, mẹ may cho tôi một cái váy.",
          note:"Mệnh đề <strong>when I was a child</strong> có chủ ngữ và bổ ngữ riêng — <em>a child</em> nói về <em>I</em>, không nói về <em>My mother</em>. Cả mệnh đề lẫn <em>for me</em> đều tháo ra được.",
          tokens:[
            {w:"My", pos:"DET", func:"det", head:1},
            {w:"mother", pos:"NOUN", func:"subj", head:2},
            {w:"made", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:4},
            {w:"dress", pos:"NOUN", func:"dObj", head:2},
            {w:"for", pos:"ADP", func:"adjunct", head:2},
            {w:"me", pos:"PRON", func:"pComp", head:5},
            {w:"when", pos:"ADP", func:"adjunct", head:2},
            {w:"I", pos:"PRON", func:"subj", head:9},
            {w:"was", pos:"VERB", func:"pComp", head:7},
            {w:"a", pos:"DET", func:"det", head:11},
            {w:"child", pos:"NOUN", func:"sComp", head:9}
          ]
        },
        {
          pattern:"S V O + mệnh đề", en:"She makes a phone call every time she arrives safely.",
          vi:"Cứ mỗi lần tới nơi an toàn là cô ấy gọi một cuộc điện thoại.",
          note:"<em>make a phone call</em> đứng thay cho <em>call</em>. <strong>every time…</strong> làm trạng ngữ chỉ tần suất, và bản thân nó kéo theo một mệnh đề đủ chủ ngữ lẫn trạng từ.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"makes", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:4},
            {w:"phone", pos:"NOUN", func:"mod", head:4},
            {w:"call", pos:"NOUN", func:"dObj", head:1},
            {w:"every", pos:"DET", func:"det", head:6},
            {w:"time", pos:"NOUN", func:"adjunct", head:1},
            {w:"she", pos:"PRON", func:"subj", head:8},
            {w:"arrives", pos:"VERB", func:"mod", head:6},
            {w:"safely", pos:"ADV", func:"adjunct", head:8}
          ]
        }
      ]
    },
    {
      verb:"put", pattern:"S V O A*", en:"I put the keys on the table.",
      vi:"Tôi để chùm chìa khóa trên bàn.",
      note:"<em>put</em> đòi cả tân ngữ <strong>và</strong> nơi chốn. <em>I put the keys</em> là câu sai — nên <strong>on the table</strong> là bổ ngữ, không phải trạng ngữ tùy ý. Bên trong nó, <em>the table</em> lại là bổ ngữ của giới từ <em>on</em>.",
      tokens:[
        {w:"I", pos:"PRON", func:"subj", head:1},
        {w:"put", pos:"VERB", func:"verb", head:-1},
        {w:"the", pos:"DET", func:"det", head:3},
        {w:"keys", pos:"NOUN", func:"dObj", head:1},
        {w:"on", pos:"ADP", func:"aComp", head:1},
        {w:"the", pos:"DET", func:"det", head:6},
        {w:"table", pos:"NOUN", func:"pComp", head:4}
      ],
      ex:[
        {
          pattern:"S V prt O", en:"He put off the meeting.",
          vi:"Anh ấy hoãn cuộc họp.",
          note:"<strong>off</strong> trông giống giới từ nhưng không phải: nó là <strong>tiểu từ động từ</strong>, dính vào <em>put</em> để thành nghĩa mới “trì hoãn”. Dấu nhận biết: nó không dẫn ra danh từ nào của riêng nó, khác hẳn <em>on</em> ở câu chính.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"put", pos:"VERB", func:"verb", head:-1},
            {w:"off", pos:"PART", func:"prt", head:1},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"meeting", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V O A", en:"It put me in a bad mood.",
          vi:"Chuyện đó làm tôi bực cả người.",
          note:"Cùng khung với câu chính — tân ngữ cộng một chỗ “đặt vào” — nhưng chỗ ấy là <strong>trạng thái</strong> chứ không phải nơi chốn thật. <em>put</em> giữ nguyên hình dạng ngữ pháp kể cả khi nghĩa đã thành trừu tượng.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:1},
            {w:"put", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"dObj", head:1},
            {w:"in", pos:"ADP", func:"aComp", head:1},
            {w:"a", pos:"DET", func:"det", head:6},
            {w:"bad", pos:"ADJ", func:"mod", head:6},
            {w:"mood", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"V O A (mệnh lệnh)", en:"Put your name at the top.",
          vi:"Viết tên bạn lên đầu trang.",
          note:"Nghĩa “viết xuống”. Vẫn nguyên đòi hỏi của <em>put</em>: có tân ngữ thì phải có chỗ đặt — <em>Put your name</em> là câu chưa xong.",
          tokens:[
            {w:"Put", pos:"VERB", func:"verb", head:-1},
            {w:"your", pos:"DET", func:"det", head:2},
            {w:"name", pos:"NOUN", func:"dObj", head:0},
            {w:"at", pos:"ADP", func:"aComp", head:0},
            {w:"the", pos:"DET", func:"det", head:5},
            {w:"top", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V O A", en:"She put a lot of effort into it.",
          vi:"Cô ấy dồn rất nhiều công sức vào đó.",
          note:"Thứ được “đặt” không còn là vật mà là công sức, và chỗ đặt cũng không còn là nơi chốn. Khung thì vẫn y nguyên — đó là điều đáng nhớ về <em>put</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"put", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"lot", pos:"NOUN", func:"dObj", head:1},
            {w:"of", pos:"ADP", func:"mod", head:3},
            {w:"effort", pos:"NOUN", func:"pComp", head:4},
            {w:"into", pos:"ADP", func:"aComp", head:1},
            {w:"it", pos:"PRON", func:"pComp", head:6}
          ]
        },
        {
          pattern:"S V O prt (tách ra)", en:"They put the meeting off until Friday.",
          vi:"Họ dời cuộc họp sang thứ Sáu.",
          note:"Chính là <em>put off</em> ở câu trước, nhưng <strong>tân ngữ chen vào giữa</strong>. Cả hai trật tự đều đúng — <em>put off the meeting</em> lẫn <em>put the meeting off</em> — và đó là dấu hiệu chắc chắn nhất để nhận ra một tiểu từ, vì giới từ không bao giờ tách ra được.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:1},
            {w:"put", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:3},
            {w:"meeting", pos:"NOUN", func:"dObj", head:1},
            {w:"off", pos:"PART", func:"prt", head:1},
            {w:"until", pos:"ADP", func:"adjunct", head:1},
            {w:"Friday", pos:"PROPN", func:"pComp", head:5}
          ]
        },
        {
          pattern:"S V prt prt O", en:"I cannot put up with this noise.",
          vi:"Tôi không chịu nổi tiếng ồn này nữa.",
          note:"Phrasal verb ba phần, <strong>không tách được</strong>: không nói <em>*put this noise up with</em>. Ba từ <em>put up with</em> phải đi liền, và tân ngữ luôn đứng sau cùng.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:2},
            {w:"cannot", pos:"AUX", func:"aux", head:2},
            {w:"put", pos:"VERB", func:"verb", head:-1},
            {w:"up", pos:"ADV", func:"prt", head:2},
            {w:"with", pos:"ADP", func:"aComp", head:2},
            {w:"this", pos:"DET", func:"det", head:6},
            {w:"noise", pos:"NOUN", func:"pComp", head:4}
          ]
        }
      ]
    },
    {
      verb:"run", pattern:"S V O", en:"She runs a business.",
      vi:"Cô ấy điều hành một doanh nghiệp.",
      note:"Ghi chú <em>Run</em>: đừng chỉ hiểu là “chạy bộ”. Ở đây <em>run</em> có tân ngữ hẳn hoi — <strong>a business</strong>. So với <em>Water is running</em>: cùng động từ nhưng không có tân ngữ nào.",
      tokens:[
        {w:"She", pos:"PRON", func:"subj", head:1},
        {w:"runs", pos:"VERB", func:"verb", head:-1},
        {w:"a", pos:"DET", func:"det", head:3},
        {w:"business", pos:"NOUN", func:"dObj", head:1}
      ],
      ex:[
        {
          pattern:"S V", en:"The engine is running.",
          vi:"Động cơ đang nổ máy.",
          note:"Chính là câu đối chứng mà ghi chú câu bên nhắc tới: <strong>không tân ngữ nào cả</strong>. Khung rút về đúng hai chỗ chủ ngữ – động từ, ngắn như <em>The sun sets</em>.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"engine", pos:"NOUN", func:"subj", head:3},
            {w:"is", pos:"AUX", func:"aux", head:3},
            {w:"running", pos:"VERB", func:"verb", head:-1}
          ]
        },
        {
          pattern:"S V A", en:"My car runs on electricity.",
          vi:"Xe tôi chạy bằng điện.",
          note:"<em>run on</em> = chạy bằng nhiên liệu gì. Khác <em>put off</em>, ở đây <strong>on</strong> vẫn là giới từ thật — nó dẫn ra <em>electricity</em> làm bổ ngữ của chính nó.",
          tokens:[
            {w:"My", pos:"DET", func:"det", head:1},
            {w:"car", pos:"NOUN", func:"subj", head:2},
            {w:"runs", pos:"VERB", func:"verb", head:-1},
            {w:"on", pos:"ADP", func:"aComp", head:2},
            {w:"electricity", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V O", en:"They run a small business.",
          vi:"Họ điều hành một doanh nghiệp nhỏ.",
          note:"Nghĩa “điều hành”, khung SVO đầy đủ. <strong>small</strong> chỉ là bổ nghĩa cho <em>business</em> — bỏ đi câu vẫn đứng, còn bỏ <strong>business</strong> thì sụp.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:1},
            {w:"run", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:4},
            {w:"small", pos:"ADJ", func:"mod", head:4},
            {w:"business", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V + bổ nghĩa", en:"He runs very fast.",
          vi:"Anh ấy chạy rất nhanh.",
          note:"Nghĩa đen “chạy bộ” — nghĩa mà trang này cố ý không nhấn, để dành chỗ cho các nghĩa khác. Không tân ngữ nào; <strong>fast</strong> là trạng từ nói về cách chạy, còn <strong>very</strong> lại bổ nghĩa cho chính <em>fast</em>.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"runs", pos:"VERB", func:"verb", head:-1},
            {w:"very", pos:"ADV", func:"mod", head:3},
            {w:"fast", pos:"ADV", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V + mục đích", en:"She ran to catch the bus.",
          vi:"Cô ấy chạy để kịp chuyến xe buýt.",
          note:"<strong>to catch the bus</strong> nói <em>chạy để làm gì</em>, không phải chạy cái gì — nên là bổ nghĩa mục đích, không phải tân ngữ. Phép thử: <em>She ran</em> vẫn trọn câu.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"ran", pos:"VERB", func:"verb", head:-1},
            {w:"to", pos:"PART", func:"to", head:3},
            {w:"catch", pos:"VERB", func:"adjunct", head:1},
            {w:"the", pos:"DET", func:"det", head:5},
            {w:"bus", pos:"NOUN", func:"dObj", head:3}
          ]
        },
        {
          pattern:"S V A*", en:"She is running for president.",
          vi:"Cô ấy đang tranh cử tổng thống.",
          note:"Nghĩa “ứng cử”. Chức vụ <strong>không</strong> phải tân ngữ — nó nằm sau <em>for</em>. Bỏ giới ngữ đi thì <em>She is running</em> quay về nghĩa chạy bộ, nên đây là bổ ngữ bắt buộc.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:2},
            {w:"is", pos:"AUX", func:"aux", head:2},
            {w:"running", pos:"VERB", func:"verb", head:-1},
            {w:"for", pos:"ADP", func:"aComp", head:2},
            {w:"president", pos:"NOUN", func:"pComp", head:3}
          ]
        }
      ]
    },
    {
      verb:"set", pattern:"S V", en:"The sun sets.",
      vi:"Mặt trời lặn.",
      note:"Khung ngắn nhất có thể: chỉ chủ ngữ và động từ, <strong>không tân ngữ, không bổ ngữ</strong>. Vẫn động từ ấy, <em>set an alarm</em> lại đòi tân ngữ — khung câu do <em>nghĩa đang dùng</em> quyết định, không phải do bản thân từ.",
      tokens:[
        {w:"The", pos:"DET", func:"det", head:1},
        {w:"sun", pos:"NOUN", func:"subj", head:2},
        {w:"sets", pos:"VERB", func:"verb", head:-1}
      ],
      ex:[
        {
          pattern:"V O (mệnh lệnh)", en:"Set an alarm.",
          vi:"Đặt báo thức đi.",
          note:"Đúng câu mà ghi chú bên cạnh hứa hẹn. Cùng một từ <em>set</em>, nhưng nghĩa “thiết lập” <strong>đòi tân ngữ</strong> — đặt hai đồ thị cạnh nhau là thấy ngay khung câu đi theo nghĩa chứ không đi theo từ.",
          tokens:[
            {w:"Set", pos:"VERB", func:"verb", head:-1},
            {w:"an", pos:"DET", func:"det", head:2},
            {w:"alarm", pos:"NOUN", func:"dObj", head:0}
          ]
        },
        {
          pattern:"bị động", en:"The film is set in Hanoi.",
          vi:"Bộ phim lấy bối cảnh Hà Nội.",
          note:"<em>be set in</em> — bối cảnh đặt ở đâu. Về hình thức là thể bị động, nhưng <strong>in Hanoi</strong> bỏ đi thì câu mất nghĩa, nên nó là bổ ngữ bắt buộc chứ không phải trạng ngữ tùy ý như <em>by John</em>.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"film", pos:"NOUN", func:"subj", head:3},
            {w:"is", pos:"AUX", func:"aux", head:3},
            {w:"set", pos:"VERB", func:"verb", head:-1},
            {w:"in", pos:"ADP", func:"aComp", head:3},
            {w:"Hanoi", pos:"PROPN", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V O A", en:"She set the table for dinner.",
          vi:"Cô ấy bày bàn ăn tối.",
          note:"<em>set the table</em> = bày bát đũa, không phải “đặt cái bàn xuống”. <strong>for dinner</strong> bỏ đi vẫn còn câu đúng nên là trạng ngữ tùy ý.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"set", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:3},
            {w:"table", pos:"NOUN", func:"dObj", head:1},
            {w:"for", pos:"ADP", func:"adjunct", head:1},
            {w:"dinner", pos:"NOUN", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V O", en:"She set a new world record.",
          vi:"Cô ấy lập kỷ lục thế giới mới.",
          note:"Nghĩa “xác lập”. Hai bổ nghĩa xếp chồng trước danh từ chính: <em>new</em> và <em>world</em> đều bám vào <strong>record</strong>, tháo hết vẫn còn <em>She set a record</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"set", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:5},
            {w:"new", pos:"ADJ", func:"mod", head:5},
            {w:"world", pos:"NOUN", func:"mod", head:5},
            {w:"record", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V O A", en:"They set fire to the building.",
          vi:"Họ phóng hỏa tòa nhà.",
          note:"Cụm cố định <em>set fire to</em>. Thứ bị cháy <strong>không</strong> phải tân ngữ — tân ngữ là <strong>fire</strong>, còn tòa nhà nằm sau giới từ <em>to</em>. So với <em>set the building on fire</em>: cùng nghĩa, đảo hẳn hai vai.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:1},
            {w:"set", pos:"VERB", func:"verb", head:-1},
            {w:"fire", pos:"NOUN", func:"dObj", head:1},
            {w:"to", pos:"ADP", func:"aComp", head:1},
            {w:"the", pos:"DET", func:"det", head:5},
            {w:"building", pos:"NOUN", func:"pComp", head:3}
          ]
        }
      ]
    },
    {
      verb:"take", pattern:"S V O C", en:"It takes 30 minutes to cook.",
      vi:"Mất 30 phút để nấu ăn.",
      note:"<strong>It</strong> là chủ ngữ giả — nó không chỉ vật gì cả, chỉ giữ chỗ cho vị trí chủ ngữ. Việc thật sự nằm ở <strong>to cook</strong>. Đây là khung <em>It takes + thời gian + to do something</em> trong ghi chú <em>Take</em>.",
      tokens:[
        {w:"It", pos:"PRON", func:"expl", head:1},
        {w:"takes", pos:"VERB", func:"verb", head:-1},
        {w:"30", pos:"NUM", func:"mod", head:3},
        {w:"minutes", pos:"NOUN", func:"dObj", head:1},
        {w:"to", pos:"PART", func:"to", head:5},
        {w:"cook", pos:"VERB", func:"cComp", head:1}
      ],
      ex:[
        {
          pattern:"S V O", en:"We took a taxi.",
          vi:"Chúng tôi bắt taxi.",
          note:"Khung SVO thường, <strong>chủ ngữ là người thật</strong> chứ không phải <em>It</em> giữ chỗ. Đặt cạnh câu chính để thấy cùng một từ <em>take</em> dựng ra hai bộ khung khác hẳn nhau.",
          tokens:[
            {w:"We", pos:"PRON", func:"subj", head:1},
            {w:"took", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"taxi", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V IO DO C", en:"It took me three days to finish.",
          vi:"Tôi mất ba ngày mới xong.",
          note:"Câu chính có thêm người: <strong>me</strong> chen vào làm tân ngữ gián tiếp. <em>It</em> vẫn là chủ ngữ giả, <strong>to finish</strong> vẫn là việc thật — khung dài ra một nút nhưng xương vẫn thế.",
          tokens:[
            {w:"It", pos:"PRON", func:"expl", head:1},
            {w:"took", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"iObj", head:1},
            {w:"three", pos:"NUM", func:"mod", head:4},
            {w:"days", pos:"NOUN", func:"dObj", head:1},
            {w:"to", pos:"PART", func:"to", head:6},
            {w:"finish", pos:"VERB", func:"cComp", head:1}
          ]
        },
        {
          pattern:"V O A (mệnh lệnh)", en:"Please take this letter to the post office.",
          vi:"Làm ơn mang lá thư này tới bưu điện.",
          note:"Nghĩa gốc nhất của <em>take</em>: cầm một vật đi chỗ khác. Có tân ngữ <strong>this letter</strong> và một nơi đến <strong>to the post office</strong> — bỏ nơi đến thì câu cụt, nên đó là bổ ngữ chứ không phải trạng ngữ tùy ý.",
          tokens:[
            {w:"Please", pos:"ADV", func:"adjunct", head:1},
            {w:"take", pos:"VERB", func:"verb", head:-1},
            {w:"this", pos:"DET", func:"det", head:3},
            {w:"letter", pos:"NOUN", func:"dObj", head:1},
            {w:"to", pos:"ADP", func:"aComp", head:1},
            {w:"the", pos:"DET", func:"det", head:7},
            {w:"post", pos:"NOUN", func:"mod", head:7},
            {w:"office", pos:"NOUN", func:"pComp", head:4}
          ]
        },
        {
          pattern:"S V O A", en:"She took the bus to school this morning.",
          vi:"Sáng nay cô ấy đi xe buýt tới trường.",
          note:"<em>take</em> + phương tiện. Câu chỉ cần <em>She took the bus</em> là đủ, nên cả <strong>to school</strong> lẫn <strong>this morning</strong> đều là trạng ngữ tùy ý — tháo ra vẫn còn câu đúng.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"took", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:3},
            {w:"bus", pos:"NOUN", func:"dObj", head:1},
            {w:"to", pos:"ADP", func:"adjunct", head:1},
            {w:"school", pos:"NOUN", func:"pComp", head:4},
            {w:"this", pos:"DET", func:"det", head:7},
            {w:"morning", pos:"NOUN", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V O A", en:"He always takes his medicine after breakfast.",
          vi:"Anh ấy luôn uống thuốc sau bữa sáng.",
          note:"Nghĩa “uống thuốc” — tiếng Anh dùng <em>take</em> chứ không dùng <em>drink</em> hay <em>eat</em>. <strong>always</strong> và <strong>after breakfast</strong> đều bỏ được.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:2},
            {w:"always", pos:"ADV", func:"adjunct", head:2},
            {w:"takes", pos:"VERB", func:"verb", head:-1},
            {w:"his", pos:"DET", func:"det", head:4},
            {w:"medicine", pos:"NOUN", func:"dObj", head:2},
            {w:"after", pos:"ADP", func:"adjunct", head:2},
            {w:"breakfast", pos:"NOUN", func:"pComp", head:5}
          ]
        },
        {
          pattern:"S V O A", en:"They take a walk together every evening.",
          vi:"Tối nào họ cũng đi dạo cùng nhau.",
          note:"<em>take</em> rỗng nghĩa: <em>take a walk</em> thật ra chỉ là <em>walk</em>. Danh từ hành động <strong>a walk</strong> mới là chỗ chứa nghĩa, còn <em>take</em> chỉ giữ chỗ động từ.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:1},
            {w:"take", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"walk", pos:"NOUN", func:"dObj", head:1},
            {w:"together", pos:"ADV", func:"adjunct", head:1},
            {w:"every", pos:"DET", func:"det", head:6},
            {w:"evening", pos:"NOUN", func:"adjunct", head:1}
          ]
        }
      ]
    },
    {
      verb:"be", pattern:"S V C", en:"She is a teacher.",
      vi:"Cô ấy là giáo viên.",
      note:"<strong>a teacher</strong> không phải tân ngữ mà là bổ ngữ chủ ngữ: nó nói <em>She là ai</em>. Động từ nối (be, seem, look, become…) không có tân ngữ, chỉ có bổ ngữ.",
      tokens:[
        {w:"She", pos:"PRON", func:"subj", head:1},
        {w:"is", pos:"VERB", func:"verb", head:-1},
        {w:"a", pos:"DET", func:"det", head:3},
        {w:"teacher", pos:"NOUN", func:"sComp", head:1}
      ],
      ex:[
        {
          pattern:"thì tiếp diễn", en:"She is singing.",
          vi:"Cô ấy đang hát.",
          note:"Cùng chữ <em>is</em>, nhưng lần này nó <strong>không nối gì cả</strong> — nó là trợ động từ, và gốc câu dời sang <strong>singing</strong>. So hai đồ thị là thấy ngay <em>be</em> đổi vai.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:2},
            {w:"is", pos:"AUX", func:"aux", head:2},
            {w:"singing", pos:"VERB", func:"verb", head:-1}
          ]
        },
        {
          pattern:"chủ ngữ giả", en:"There is a problem.",
          vi:"Có một vấn đề.",
          note:"<strong>There</strong> không trỏ vào chỗ nào cả — nó chỉ giữ chỗ chủ ngữ, giống <em>It</em> trong <em>It takes 30 minutes</em>. Thứ thật sự tồn tại là <strong>a problem</strong>, đứng sau động từ.",
          tokens:[
            {w:"There", pos:"PRON", func:"expl", head:1},
            {w:"is", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"problem", pos:"NOUN", func:"sComp", head:1}
          ]
        },
        {
          pattern:"S V C", en:"The soup is cold.",
          vi:"Món súp bị nguội.",
          note:"Bổ ngữ chủ ngữ là <strong>tính từ</strong> chứ không phải danh từ như <em>a teacher</em>. Cùng một ô bổ ngữ, hai loại từ khác nhau — đó là lý do ô này gọi là “bổ ngữ” chứ không gọi là “tân ngữ”.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"soup", pos:"NOUN", func:"subj", head:2},
            {w:"is", pos:"VERB", func:"verb", head:-1},
            {w:"cold", pos:"ADJ", func:"sComp", head:2}
          ]
        },
        {
          pattern:"S V A*", en:"The keys are on the table.",
          vi:"Chùm chìa khóa ở trên bàn.",
          note:"<em>be</em> chỉ nơi chốn. Giới ngữ <strong>on the table</strong> bỏ đi thì <em>The keys are</em> không còn là câu — nên nó là bổ ngữ bắt buộc, khác hẳn <em>on the table</em> trong câu <em>put</em> vốn cũng bắt buộc nhưng vì lý do khác.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"keys", pos:"NOUN", func:"subj", head:2},
            {w:"are", pos:"VERB", func:"verb", head:-1},
            {w:"on", pos:"ADP", func:"aComp", head:2},
            {w:"the", pos:"DET", func:"det", head:5},
            {w:"table", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"bị động", en:"The window was broken.",
          vi:"Cửa sổ bị vỡ.",
          note:"Thể bị động rút gọn: không nói ai làm. <strong>was</strong> là trợ động từ, gốc câu nằm ở <strong>broken</strong>. So với <em>The letter was written by John</em> — thêm <em>by…</em> là nói ra người làm, nhưng phần đó bỏ được.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"window", pos:"NOUN", func:"subj", head:3},
            {w:"was", pos:"AUX", func:"aux", head:3},
            {w:"broken", pos:"VERB", func:"verb", head:-1}
          ]
        }
      ]
    },
    {
      verb:"give", pattern:"S V IO DO", en:"She gave me a book.",
      vi:"Cô ấy đưa tôi một quyển sách.",
      note:"Động từ cho – tặng kinh điển, cùng khung với <em>do me a favor</em>: <strong>me</strong> gián tiếp, <strong>a book</strong> trực tiếp, cả hai đều bắt buộc.",
      tokens:[
        {w:"She", pos:"PRON", func:"subj", head:1},
        {w:"gave", pos:"VERB", func:"verb", head:-1},
        {w:"me", pos:"PRON", func:"iObj", head:1},
        {w:"a", pos:"DET", func:"det", head:4},
        {w:"book", pos:"NOUN", func:"dObj", head:1}
      ],
      ex:[
        {
          pattern:"S V DO + to ai", en:"She gave a book to me.",
          vi:"Cô ấy đưa quyển sách cho tôi.",
          note:"Vẫn nghĩa câu trên, đảo lại. Người nhận tụt xuống thành <strong>giới ngữ <em>to me</em></strong> chứ không còn là tân ngữ gián tiếp. Lật qua lật lại hai đồ thị là thấy rõ cùng một ý có hai bộ khung.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"gave", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"book", pos:"NOUN", func:"dObj", head:1},
            {w:"to", pos:"ADP", func:"aComp", head:1},
            {w:"me", pos:"PRON", func:"pComp", head:4}
          ]
        },
        {
          pattern:"V IO DO (mệnh lệnh)", en:"Give it a try.",
          vi:"Cứ thử xem.",
          note:"<em>give</em> rỗng nghĩa: cả câu thật ra chỉ có nghĩa <em>try it</em>. Khung hai tân ngữ vẫn nguyên vẹn, nhưng <strong>a try</strong> là một danh từ hành động đứng thay chính động từ ấy.",
          tokens:[
            {w:"Give", pos:"VERB", func:"verb", head:-1},
            {w:"it", pos:"PRON", func:"iObj", head:0},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"try", pos:"NOUN", func:"dObj", head:0}
          ]
        },
        {
          pattern:"S V IO DO", en:"The noise gave me a headache.",
          vi:"Tiếng ồn làm tôi đau đầu.",
          note:"Nghĩa “gây ra”. Không ai trao cho ai vật gì cả, nhưng khung <strong>hai tân ngữ</strong> vẫn nguyên vẹn — người chịu đứng trước, thứ gây ra đứng sau.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"noise", pos:"NOUN", func:"subj", head:2},
            {w:"gave", pos:"VERB", func:"verb", head:-1},
            {w:"me", pos:"PRON", func:"iObj", head:2},
            {w:"a", pos:"DET", func:"det", head:5},
            {w:"headache", pos:"NOUN", func:"dObj", head:2}
          ]
        },
        {
          pattern:"S V O A", en:"He gave a speech at the conference.",
          vi:"Anh ấy đọc diễn văn tại hội nghị.",
          note:"<em>give a speech</em> đứng thay cho <em>speak</em>. Lần này chỉ có <strong>một</strong> tân ngữ — không có người nhận, nên khung rút từ bốn ô xuống ba.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"gave", pos:"VERB", func:"verb", head:-1},
            {w:"a", pos:"DET", func:"det", head:3},
            {w:"speech", pos:"NOUN", func:"dObj", head:1},
            {w:"at", pos:"ADP", func:"adjunct", head:1},
            {w:"the", pos:"DET", func:"det", head:6},
            {w:"conference", pos:"NOUN", func:"pComp", head:4}
          ]
        }
      ]
    },
    {
      verb:"come", pattern:"S V A*", en:"My friends came to my party.",
      vi:"Bạn tôi đến dự tiệc của tôi.",
      note:"Cặp đối của <em>go</em>, và khác nhau ở <strong>chỗ đứng của người nói</strong> chứ không ở hướng đi: <em>come</em> là tiến về phía người nói hoặc người nghe. Cùng một chuyến đi, chủ nhà nói <em>come</em>, khách nói <em>go</em>.",
      tokens:[
        {w:"My", pos:"DET", func:"det", head:1},
        {w:"friends", pos:"NOUN", func:"subj", head:2},
        {w:"came", pos:"VERB", func:"verb", head:-1},
        {w:"to", pos:"ADP", func:"aComp", head:2},
        {w:"my", pos:"DET", func:"det", head:5},
        {w:"party", pos:"NOUN", func:"pComp", head:3}
      ],
      ex:[
        {
          pattern:"S V C", en:"Her dream came true.",
          vi:"Ước mơ của cô ấy đã thành hiện thực.",
          note:"<em>come</em> làm động từ nối, y như <em>go sour</em> hay <em>get cold</em> — nhưng có một nếp quen: <strong>come</strong> thường dẫn sang trạng thái <em>tốt</em> (<em>come true</em>, <em>come right</em>), còn <em>go</em> thường dẫn sang trạng thái xấu.",
          tokens:[
            {w:"Her", pos:"DET", func:"det", head:1},
            {w:"dream", pos:"NOUN", func:"subj", head:2},
            {w:"came", pos:"VERB", func:"verb", head:-1},
            {w:"true", pos:"ADJ", func:"sComp", head:2}
          ]
        },
        {
          pattern:"S V", en:"A big storm is coming.",
          vi:"Một cơn bão lớn đang tới.",
          note:"Khung ngắn nhất: không tân ngữ, không bổ ngữ, không cả nơi đến. <em>come</em> tự nó đã đủ nghĩa “đang tiến lại gần” — chỗ cần đến được hiểu ngầm là chỗ người nói đang đứng.",
          tokens:[
            {w:"A", pos:"DET", func:"det", head:2},
            {w:"big", pos:"ADJ", func:"mod", head:2},
            {w:"storm", pos:"NOUN", func:"subj", head:4},
            {w:"is", pos:"AUX", func:"aux", head:4},
            {w:"coming", pos:"VERB", func:"verb", head:-1}
          ]
        },
        {
          pattern:"S V A*", en:"She comes from Vietnam.",
          vi:"Cô ấy là người Việt Nam.",
          note:"<em>come from</em> = quê quán, xuất xứ — nghĩa đã rời hẳn khỏi chuyện di chuyển. <strong>from Vietnam</strong> bỏ đi thì <em>She comes</em> thành câu khác nghĩa hẳn, nên nó là bổ ngữ bắt buộc.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"comes", pos:"VERB", func:"verb", head:-1},
            {w:"from", pos:"ADP", func:"aComp", head:1},
            {w:"Vietnam", pos:"PROPN", func:"pComp", head:2}
          ]
        },
        {
          pattern:"S V A*", en:"It comes in three sizes.",
          vi:"Nó có ba cỡ.",
          note:"<em>come in</em> = có bán ở dạng nào, cỡ nào, màu nào. Chủ ngữ là món hàng, và <em>come</em> ở đây gần như mất hẳn nghĩa di chuyển.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:1},
            {w:"comes", pos:"VERB", func:"verb", head:-1},
            {w:"in", pos:"ADP", func:"aComp", head:1},
            {w:"three", pos:"NUM", func:"mod", head:4},
            {w:"sizes", pos:"NOUN", func:"pComp", head:2}
          ]
        },
        {
          pattern:"S V C", en:"Safety comes first.",
          vi:"An toàn là trên hết.",
          note:"Nghĩa “xếp thứ mấy”. <strong>first</strong> là bổ ngữ chủ ngữ — nó nói về <em>Safety</em>, không nói về cách <em>come</em> diễn ra.",
          tokens:[
            {w:"Safety", pos:"NOUN", func:"subj", head:1},
            {w:"comes", pos:"VERB", func:"verb", head:-1},
            {w:"first", pos:"ADV", func:"sComp", head:1}
          ]
        },
        {
          pattern:"V + V (mệnh lệnh)", en:"Come see this.",
          vi:"Lại xem cái này đi.",
          note:"Lối nói thân mật: đầy đủ là <em>Come and see this</em>, nhưng người bản ngữ bỏ luôn <em>and</em>. Hai động từ đứng liền nhau, không có <em>to</em> ở giữa.",
          tokens:[
            {w:"Come", pos:"VERB", func:"verb", head:-1},
            {w:"see", pos:"VERB", func:"adjunct", head:0},
            {w:"this", pos:"PRON", func:"dObj", head:1}
          ]
        }
      ]
    },
    {
      verb:"become", pattern:"S V C", en:"She became a doctor.",
      vi:"Cô ấy trở thành bác sĩ.",
      note:"Động từ nối thuần nhất cho sự thay đổi. Nét riêng của nó: nhận được <strong>cả danh từ lẫn tính từ</strong> — <em>became a doctor</em> và <em>became cold</em> đều đúng. <em>get</em>, <em>go</em>, <em>turn</em> thì chỉ đi với tính từ.",
      tokens:[
        {w:"She", pos:"PRON", func:"subj", head:1},
        {w:"became", pos:"VERB", func:"verb", head:-1},
        {w:"a", pos:"DET", func:"det", head:3},
        {w:"doctor", pos:"NOUN", func:"sComp", head:1}
      ],
      ex:[
        {
          pattern:"S V C", en:"It became very cold.",
          vi:"Trời trở nên rất lạnh.",
          note:"Cũng từ ấy nhưng bổ ngữ là tính từ. Đặt cạnh <em>It is getting cold</em>: cùng nghĩa, <em>become</em> trang trọng hơn, <em>get</em> đời thường hơn.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:1},
            {w:"became", pos:"VERB", func:"verb", head:-1},
            {w:"very", pos:"ADV", func:"mod", head:3},
            {w:"cold", pos:"ADJ", func:"sComp", head:1}
          ]
        },
        {
          pattern:"S V C A", en:"He became interested in music.",
          vi:"Anh ấy đâm ra thích âm nhạc.",
          note:"Bổ ngữ là phân từ dùng như tính từ, và nó kéo theo giới ngữ của riêng mình — <strong>in music</strong> thuộc về <em>interested</em>, không thuộc về <em>became</em>.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"became", pos:"VERB", func:"verb", head:-1},
            {w:"interested", pos:"ADJ", func:"sComp", head:1},
            {w:"in", pos:"ADP", func:"mod", head:2},
            {w:"music", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V C", en:"They soon became good friends.",
          vi:"Chẳng bao lâu họ thành bạn thân.",
          note:"Bổ ngữ danh từ số nhiều, không có mạo từ. <strong>soon</strong> bỏ đi câu vẫn đúng nên là trạng ngữ tùy ý.",
          tokens:[
            {w:"They", pos:"PRON", func:"subj", head:2},
            {w:"soon", pos:"ADV", func:"adjunct", head:2},
            {w:"became", pos:"VERB", func:"verb", head:-1},
            {w:"good", pos:"ADJ", func:"mod", head:4},
            {w:"friends", pos:"NOUN", func:"sComp", head:2}
          ]
        },
        {
          pattern:"S V A*", en:"What became of him?",
          vi:"Rồi anh ta ra sao?",
          note:"Cụm cố định <em>become of</em> = rồi ra sao, số phận thế nào. Ở đây <em>become</em> không còn là động từ nối nữa: <strong>of him</strong> là bổ ngữ bắt buộc, không phải bổ ngữ chủ ngữ.",
          tokens:[
            {w:"What", pos:"PRON", func:"subj", head:1},
            {w:"became", pos:"VERB", func:"verb", head:-1},
            {w:"of", pos:"ADP", func:"aComp", head:1},
            {w:"him", pos:"PRON", func:"pComp", head:2}
          ]
        }
      ]
    },
    {
      verb:"seem", pattern:"S V C", en:"He seems tired.",
      vi:"Anh ấy có vẻ mệt.",
      note:"Động từ nối của sự phỏng đoán — nói điều mình <em>suy ra</em> chứ không khẳng định. Cùng ô bổ ngữ với <em>You look tired</em>, khác ở chỗ <em>look</em> dựa vào mắt nhìn còn <em>seem</em> dựa vào suy đoán chung.",
      tokens:[
        {w:"He", pos:"PRON", func:"subj", head:1},
        {w:"seems", pos:"VERB", func:"verb", head:-1},
        {w:"tired", pos:"ADJ", func:"sComp", head:1}
      ],
      ex:[
        {
          pattern:"S V C(to be)", en:"She seems to be happy.",
          vi:"Cô ấy có vẻ đang vui.",
          note:"Thêm <strong>to be</strong> vào giữa — nghĩa gần như không đổi, chỉ trang trọng hơn. Bổ ngữ thật <em>happy</em> giờ treo vào <em>be</em> chứ không treo thẳng vào <em>seem</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"seems", pos:"VERB", func:"verb", head:-1},
            {w:"to", pos:"PART", func:"to", head:3},
            {w:"be", pos:"VERB", func:"cComp", head:1},
            {w:"happy", pos:"ADJ", func:"sComp", head:3}
          ]
        },
        {
          pattern:"S V C(mệnh đề)", en:"It seems that they have left.",
          vi:"Có vẻ như họ đã đi rồi.",
          note:"Khi bổ ngữ là cả một mệnh đề thì phải mượn <strong>It</strong> làm chủ ngữ giả — không nói được <em>*Seems that they have left</em> ở văn viết. Cùng khuôn với <em>It looks as if…</em>",
          tokens:[
            {w:"It", pos:"PRON", func:"expl", head:1},
            {w:"seems", pos:"VERB", func:"verb", head:-1},
            {w:"that", pos:"ADP", func:"sComp", head:1},
            {w:"they", pos:"PRON", func:"subj", head:5},
            {w:"have", pos:"AUX", func:"aux", head:5},
            {w:"left", pos:"VERB", func:"pComp", head:2}
          ]
        },
        {
          pattern:"chủ ngữ giả there", en:"There seems to be a problem.",
          vi:"Hình như có vấn đề.",
          note:"Hai chủ ngữ giả chồng nhau: <strong>There</strong> giữ chỗ chủ ngữ, còn thứ thật sự tồn tại là <em>a problem</em> nằm tuốt phía sau. Đây là cách nói nhẹ đi của <em>There is a problem</em>.",
          tokens:[
            {w:"There", pos:"PRON", func:"expl", head:1},
            {w:"seems", pos:"VERB", func:"verb", head:-1},
            {w:"to", pos:"PART", func:"to", head:3},
            {w:"be", pos:"VERB", func:"cComp", head:1},
            {w:"a", pos:"DET", func:"det", head:5},
            {w:"problem", pos:"NOUN", func:"sComp", head:3}
          ]
        },
        {
          pattern:"S V C", en:"It seems like a good idea.",
          vi:"Nghe có vẻ là ý hay.",
          note:"<em>seem like</em> + danh từ, y như <em>look like</em>. <strong>like</strong> là giới từ chứ không phải động từ “thích” — dấu hiệu: ngay sau nó là một cụm danh từ.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:1},
            {w:"seems", pos:"VERB", func:"verb", head:-1},
            {w:"like", pos:"ADP", func:"sComp", head:1},
            {w:"a", pos:"DET", func:"det", head:5},
            {w:"good", pos:"ADJ", func:"mod", head:5},
            {w:"idea", pos:"NOUN", func:"pComp", head:2}
          ]
        }
      ]
    },
    {
      verb:"feel", pattern:"S V C", en:"I feel tired.",
      vi:"Tôi thấy mệt.",
      note:"Ở đây <em>feel</em> là động từ nối nên đi với <strong>tính từ</strong>. Đó là lý do <em>I feel bad</em> mới đúng, còn <em>I feel badly</em> thì sai — cùng cái bẫy với <em>You look tiredly</em>.",
      tokens:[
        {w:"I", pos:"PRON", func:"subj", head:1},
        {w:"feel", pos:"VERB", func:"verb", head:-1},
        {w:"tired", pos:"ADJ", func:"sComp", head:1}
      ],
      ex:[
        {
          pattern:"S V O", en:"She felt the soft fabric.",
          vi:"Cô ấy sờ vào lớp vải mềm.",
          note:"Cùng từ ấy nhưng giờ là động từ thường, có <strong>tân ngữ thật</strong> bị tác động. So với câu chính: một bên nói về chủ ngữ, một bên chạm vào vật — hai khung khác hẳn.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"felt", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"soft", pos:"ADJ", func:"mod", head:4},
            {w:"fabric", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V C", en:"This shirt feels soft.",
          vi:"Cái áo này sờ vào thấy mềm.",
          note:"Chủ ngữ là <strong>vật</strong>, không phải người cảm nhận. Tiếng Việt phải thêm “sờ vào” mới xuôi, tiếng Anh thì động từ nối gánh luôn ý đó.",
          tokens:[
            {w:"This", pos:"DET", func:"det", head:1},
            {w:"shirt", pos:"NOUN", func:"subj", head:2},
            {w:"feels", pos:"VERB", func:"verb", head:-1},
            {w:"soft", pos:"ADJ", func:"sComp", head:2}
          ]
        },
        {
          pattern:"S V A*", en:"I feel like going home.",
          vi:"Tôi muốn về nhà.",
          note:"<em>feel like</em> = muốn, thích làm gì. <strong>like</strong> là giới từ nên sau nó là <strong>V-ing</strong>, không phải nguyên thể — cùng bẫy với <em>look forward to</em>.",
          tokens:[
            {w:"I", pos:"PRON", func:"subj", head:1},
            {w:"feel", pos:"VERB", func:"verb", head:-1},
            {w:"like", pos:"ADP", func:"aComp", head:1},
            {w:"going", pos:"VERB", func:"pComp", head:2},
            {w:"home", pos:"ADV", func:"aComp", head:3}
          ]
        },
        {
          pattern:"S V O C", en:"He felt someone touch his arm.",
          vi:"Anh ấy thấy có ai chạm vào tay mình.",
          note:"<em>feel</em> là động từ tri giác, và cả họ này (<em>see</em>, <em>hear</em>, <em>watch</em>, <em>feel</em>) đều lấy <strong>động từ nguyên thể không <em>to</em></strong> — giống hệt <em>make me wait</em>. <strong>touch</strong> nói về <em>someone</em>, nên là bổ ngữ tân ngữ.",
          tokens:[
            {w:"He", pos:"PRON", func:"subj", head:1},
            {w:"felt", pos:"VERB", func:"verb", head:-1},
            {w:"someone", pos:"PRON", func:"dObj", head:1},
            {w:"touch", pos:"VERB", func:"oComp", head:1},
            {w:"his", pos:"DET", func:"det", head:5},
            {w:"arm", pos:"NOUN", func:"dObj", head:3}
          ]
        }
      ]
    },
    {
      verb:"turn", pattern:"S V C", en:"The leaves turned brown.",
      vi:"Lá cây chuyển sang màu nâu.",
      note:"Động từ nối chuyên cho <strong>màu sắc</strong> và bước ngoặt. Cùng họ với <em>go sour</em>, <em>get cold</em>, <em>become cold</em> — nhưng <em>turn</em> là từ tự nhiên nhất khi nói đổi màu.",
      tokens:[
        {w:"The", pos:"DET", func:"det", head:1},
        {w:"leaves", pos:"NOUN", func:"subj", head:2},
        {w:"turned", pos:"VERB", func:"verb", head:-1},
        {w:"brown", pos:"ADJ", func:"sComp", head:2}
      ],
      ex:[
        {
          pattern:"V prt O (mệnh lệnh)", en:"Please turn off the light.",
          vi:"Làm ơn tắt đèn.",
          note:"<strong>off</strong> là tiểu từ động từ, dính vào <em>turn</em> thành nghĩa “tắt”. Dấu nhận biết: nó không dẫn ra danh từ nào của riêng nó, và có thể tách ra — <em>turn the light off</em> cũng đúng.",
          tokens:[
            {w:"Please", pos:"ADV", func:"adjunct", head:1},
            {w:"turn", pos:"VERB", func:"verb", head:-1},
            {w:"off", pos:"PART", func:"prt", head:1},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"light", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V C", en:"She turned thirty last month.",
          vi:"Tháng trước cô ấy tròn ba mươi.",
          note:"Nghĩa “tròn bao nhiêu tuổi” — bổ ngữ là một con số. Chỉ <em>turn</em> làm được việc này; không nói <em>*became thirty</em> hay <em>*got thirty</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"turned", pos:"VERB", func:"verb", head:-1},
            {w:"thirty", pos:"NUM", func:"sComp", head:1},
            {w:"last", pos:"ADJ", func:"mod", head:4},
            {w:"month", pos:"NOUN", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V A*", en:"The rain turned into snow.",
          vi:"Mưa chuyển thành tuyết.",
          note:"<em>turn into</em> — đổi hẳn sang một thứ khác. Khác câu chính ở chỗ thứ biến thành là <strong>danh từ</strong> nên phải đi qua giới từ <em>into</em>, không đứng trần như tính từ <em>brown</em>.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"rain", pos:"NOUN", func:"subj", head:2},
            {w:"turned", pos:"VERB", func:"verb", head:-1},
            {w:"into", pos:"ADP", func:"aComp", head:2},
            {w:"snow", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"chủ ngữ giả + prt", en:"It turned out to be a mistake.",
          vi:"Hóa ra đó là một sai lầm.",
          note:"<em>turn out</em> = hóa ra là. <strong>It</strong> là chủ ngữ giả, và thứ thật sự được nói tới nằm ở <em>a mistake</em> tận cuối câu — chuỗi <em>out → to → be → a mistake</em> nối nhau từng nấc.",
          tokens:[
            {w:"It", pos:"PRON", func:"expl", head:1},
            {w:"turned", pos:"VERB", func:"verb", head:-1},
            {w:"out", pos:"ADV", func:"prt", head:1},
            {w:"to", pos:"PART", func:"to", head:4},
            {w:"be", pos:"VERB", func:"cComp", head:1},
            {w:"a", pos:"DET", func:"det", head:6},
            {w:"mistake", pos:"NOUN", func:"sComp", head:4}
          ]
        }
      ]
    },
    {
      verb:"sound", pattern:"S V C", en:"That sounds great.",
      vi:"Nghe hay đấy.",
      note:"Động từ nối của tai, cùng họ với <em>look</em> (mắt), <em>feel</em> (xúc giác). Đáng nhớ: chủ ngữ là <strong>thứ được nghe</strong> chứ không phải người nghe — <em>That sounds great</em>, không phải <em>*I sound great about that</em>.",
      tokens:[
        {w:"That", pos:"PRON", func:"subj", head:1},
        {w:"sounds", pos:"VERB", func:"verb", head:-1},
        {w:"great", pos:"ADJ", func:"sComp", head:1}
      ],
      ex:[
        {
          pattern:"S V C", en:"It sounds like a good plan.",
          vi:"Nghe như một kế hoạch hay.",
          note:"<em>sound like</em> + danh từ, hệt <em>look like</em> và <em>seem like</em>. Cả ba giác quan dùng chung một khuôn: tính từ thì đứng trần, danh từ thì phải qua <em>like</em>.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:1},
            {w:"sounds", pos:"VERB", func:"verb", head:-1},
            {w:"like", pos:"ADP", func:"sComp", head:1},
            {w:"a", pos:"DET", func:"det", head:5},
            {w:"good", pos:"ADJ", func:"mod", head:5},
            {w:"plan", pos:"NOUN", func:"pComp", head:2}
          ]
        },
        {
          pattern:"S V C A", en:"You sound tired on the phone.",
          vi:"Giọng bạn qua điện thoại nghe mệt lắm.",
          note:"Lần này chủ ngữ là người — nhưng vẫn là <strong>người được nghe</strong>, không phải người nghe. <strong>on the phone</strong> bỏ đi câu vẫn đúng nên là trạng ngữ tùy ý.",
          tokens:[
            {w:"You", pos:"PRON", func:"subj", head:1},
            {w:"sound", pos:"VERB", func:"verb", head:-1},
            {w:"tired", pos:"ADJ", func:"sComp", head:1},
            {w:"on", pos:"ADP", func:"adjunct", head:1},
            {w:"the", pos:"DET", func:"det", head:5},
            {w:"phone", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V A", en:"The alarm sounded at six.",
          vi:"Chuông báo kêu lúc sáu giờ.",
          note:"Ở đây <em>sound</em> <strong>không</strong> còn là động từ nối — nó là động từ thường nghĩa “kêu, vang lên”, không có bổ ngữ nào. Cùng một từ, hai vai hoàn toàn khác.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"alarm", pos:"NOUN", func:"subj", head:2},
            {w:"sounded", pos:"VERB", func:"verb", head:-1},
            {w:"at", pos:"ADP", func:"adjunct", head:2},
            {w:"six", pos:"NUM", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V C(mệnh đề)", en:"It sounds as if she is angry.",
          vi:"Nghe như cô ấy đang giận.",
          note:"Bổ ngữ là cả một mệnh đề nên phải mượn <strong>It</strong> giữ chỗ chủ ngữ — cùng khuôn với <em>It looks as if…</em> và <em>It seems that…</em>",
          tokens:[
            {w:"It", pos:"PRON", func:"expl", head:1},
            {w:"sounds", pos:"VERB", func:"verb", head:-1},
            {w:"as", pos:"ADV", func:"mod", head:3},
            {w:"if", pos:"ADP", func:"sComp", head:1},
            {w:"she", pos:"PRON", func:"subj", head:5},
            {w:"is", pos:"VERB", func:"pComp", head:3},
            {w:"angry", pos:"ADJ", func:"sComp", head:5}
          ]
        }
      ]
    },
    {
      verb:"smell", pattern:"S V C", en:"The soup smells delicious.",
      vi:"Nồi súp thơm quá.",
      note:"Động từ nối của mũi. Như cả họ giác quan, nó có <strong>hai vai</strong>: ở đây chủ ngữ là thứ tỏa mùi, còn ở <em>She smelled the flowers</em> thì chủ ngữ lại là người ngửi.",
      tokens:[
        {w:"The", pos:"DET", func:"det", head:1},
        {w:"soup", pos:"NOUN", func:"subj", head:2},
        {w:"smells", pos:"VERB", func:"verb", head:-1},
        {w:"delicious", pos:"ADJ", func:"sComp", head:2}
      ],
      ex:[
        {
          pattern:"S V O", en:"She smelled the flowers.",
          vi:"Cô ấy ngửi mấy bông hoa.",
          note:"Vai ngược lại: chủ ngữ là <strong>người ngửi</strong>, và có tân ngữ thật bị tác động. Cùng cặp đối lập như <em>I feel tired</em> với <em>She felt the fabric</em>.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"smelled", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:3},
            {w:"flowers", pos:"NOUN", func:"dObj", head:1}
          ]
        },
        {
          pattern:"S V C", en:"It smells like burning.",
          vi:"Có mùi khét.",
          note:"<em>smell like</em> + danh từ hoặc V-ing. <strong>like</strong> là giới từ nên <em>burning</em> ở đây là danh động từ, không phải động từ đang chia.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:1},
            {w:"smells", pos:"VERB", func:"verb", head:-1},
            {w:"like", pos:"ADP", func:"sComp", head:1},
            {w:"burning", pos:"VERB", func:"pComp", head:2}
          ]
        },
        {
          pattern:"S V A*", en:"This room smells of smoke.",
          vi:"Phòng này sặc mùi khói.",
          note:"<em>smell of</em> khác <em>smell like</em>: <em>of</em> nói mùi <strong>đúng là thứ đó</strong>, còn <em>like</em> chỉ là na ná. Đây là chỗ <em>smell</em> và <em>taste</em> có riêng mà <em>look</em>, <em>sound</em> không có.",
          tokens:[
            {w:"This", pos:"DET", func:"det", head:1},
            {w:"room", pos:"NOUN", func:"subj", head:2},
            {w:"smells", pos:"VERB", func:"verb", head:-1},
            {w:"of", pos:"ADP", func:"aComp", head:2},
            {w:"smoke", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V C A", en:"Something smells bad in here.",
          vi:"Trong này có mùi gì hôi hôi.",
          note:"Bổ ngữ là tính từ <strong>bad</strong>, không phải trạng từ <em>badly</em> — đúng luật động từ nối, y như <em>I feel bad</em>.",
          tokens:[
            {w:"Something", pos:"PRON", func:"subj", head:1},
            {w:"smells", pos:"VERB", func:"verb", head:-1},
            {w:"bad", pos:"ADJ", func:"sComp", head:1},
            {w:"in", pos:"ADP", func:"adjunct", head:1},
            {w:"here", pos:"ADV", func:"pComp", head:3}
          ]
        }
      ]
    },
    {
      verb:"taste", pattern:"S V C", en:"This soup tastes salty.",
      vi:"Món súp này mặn.",
      note:"Động từ nối của lưỡi, khép trọn bộ năm giác quan cùng <em>look</em>, <em>sound</em>, <em>smell</em>, <em>feel</em>. Cả năm dùng chung một khuôn: tính từ đứng trần, danh từ phải qua <em>like</em>.",
      tokens:[
        {w:"This", pos:"DET", func:"det", head:1},
        {w:"soup", pos:"NOUN", func:"subj", head:2},
        {w:"tastes", pos:"VERB", func:"verb", head:-1},
        {w:"salty", pos:"ADJ", func:"sComp", head:2}
      ],
      ex:[
        {
          pattern:"S V O", en:"She tasted the sauce carefully.",
          vi:"Cô ấy nếm thử nước sốt một cách cẩn thận.",
          note:"Vai người nếm: có tân ngữ thật. <strong>carefully</strong> là trạng từ và ở đây đúng — vì <em>taste</em> lúc này là động từ thường, không phải động từ nối.",
          tokens:[
            {w:"She", pos:"PRON", func:"subj", head:1},
            {w:"tasted", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"sauce", pos:"NOUN", func:"dObj", head:1},
            {w:"carefully", pos:"ADV", func:"adjunct", head:1}
          ]
        },
        {
          pattern:"S V C", en:"It tastes like chicken.",
          vi:"Ăn có vị như thịt gà.",
          note:"<em>taste like</em> — na ná vị đó chứ không phải đúng thứ đó. Câu quen thuộc nhất của cả họ giác quan.",
          tokens:[
            {w:"It", pos:"PRON", func:"subj", head:1},
            {w:"tastes", pos:"VERB", func:"verb", head:-1},
            {w:"like", pos:"ADP", func:"sComp", head:1},
            {w:"chicken", pos:"NOUN", func:"pComp", head:2}
          ]
        },
        {
          pattern:"S V A*", en:"The wine tastes of oak.",
          vi:"Rượu có vị gỗ sồi.",
          note:"<em>taste of</em> — vị <strong>đúng là</strong> thứ đó, chứ không phải giống. Đặt cạnh <em>tastes like chicken</em> để thấy <em>of</em> và <em>like</em> nói hai điều khác nhau.",
          tokens:[
            {w:"The", pos:"DET", func:"det", head:1},
            {w:"wine", pos:"NOUN", func:"subj", head:2},
            {w:"tastes", pos:"VERB", func:"verb", head:-1},
            {w:"of", pos:"ADP", func:"aComp", head:2},
            {w:"oak", pos:"NOUN", func:"pComp", head:3}
          ]
        },
        {
          pattern:"S V O", en:"Can you taste the difference?",
          vi:"Bạn có nếm ra khác biệt không?",
          note:"Câu hỏi, vai người nếm. <strong>Can</strong> là trợ động từ nên nhảy lên trước chủ ngữ, còn <em>taste</em> vẫn giữ tân ngữ của mình.",
          tokens:[
            {w:"Can", pos:"AUX", func:"aux", head:2},
            {w:"you", pos:"PRON", func:"subj", head:2},
            {w:"taste", pos:"VERB", func:"verb", head:-1},
            {w:"the", pos:"DET", func:"det", head:4},
            {w:"difference", pos:"NOUN", func:"dObj", head:2}
          ]
        }
      ]
    },
    {
      verb:"want", pattern:"S V C(mệnh đề)", en:"I want to learn English.",
      vi:"Tôi muốn học tiếng Anh.",
      note:"Bổ ngữ của <em>want</em> là cả cụm <strong>to learn English</strong>. Khác với <em>got him to fix</em>: ở đây không có tân ngữ chen vào giữa, người học chính là chủ ngữ.",
      tokens:[
        {w:"I", pos:"PRON", func:"subj", head:1},
        {w:"want", pos:"VERB", func:"verb", head:-1},
        {w:"to", pos:"PART", func:"to", head:3},
        {w:"learn", pos:"VERB", func:"cComp", head:1},
        {w:"English", pos:"NOUN", func:"dObj", head:3}
      ]
    },
    {
      verb:"smile", pattern:"S V + bổ nghĩa", en:"The old man in the corner smiled happily.",
      vi:"Ông già ngồi trong góc mỉm cười vui vẻ.",
      note:"Câu dài nhưng khung chỉ có hai chỗ: <strong>man – smiled</strong>. <em>old</em>, <em>in the corner</em>, <em>happily</em> đều là bổ nghĩa, tháo hết vẫn còn câu đúng. Bật “Chỉ khung câu” để thấy phần xương.",
      tokens:[
        {w:"The", pos:"DET", func:"det", head:2},
        {w:"old", pos:"ADJ", func:"mod", head:2},
        {w:"man", pos:"NOUN", func:"subj", head:6},
        {w:"in", pos:"ADP", func:"mod", head:2},
        {w:"the", pos:"DET", func:"det", head:5},
        {w:"corner", pos:"NOUN", func:"pComp", head:3},
        {w:"smiled", pos:"VERB", func:"verb", head:-1},
        {w:"happily", pos:"ADV", func:"adjunct", head:6}
      ]
    },
    {
      verb:"write", pattern:"bị động", en:"The letter was written by John.",
      vi:"Lá thư được John viết.",
      note:"Chủ ngữ ngữ pháp là <strong>The letter</strong>, nhưng người viết là <strong>John</strong>. Về chức vụ, <em>by John</em> chỉ là trạng ngữ tùy ý — bỏ đi vẫn còn câu đúng: <em>The letter was written</em>.",
      tokens:[
        {w:"The", pos:"DET", func:"det", head:1},
        {w:"letter", pos:"NOUN", func:"subj", head:3},
        {w:"was", pos:"AUX", func:"aux", head:3},
        {w:"written", pos:"VERB", func:"verb", head:-1},
        {w:"by", pos:"ADP", func:"adjunct", head:3},
        {w:"John", pos:"PROPN", func:"pComp", head:4}
      ]
    }
  ];

  return {GROUPS, DATA};
})();
