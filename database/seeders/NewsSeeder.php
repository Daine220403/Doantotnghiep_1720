<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user or create one for the author
        $author = User::first();

        $newsArticles = [
            [
                'title' => '5 điểm đến tuyệt vời cho mùa hè 2026',
                'description' => 'Khám phá những điểm đến đặc biệt nhất khi du lịch vào mùa hè năm nay',
                'content' => 'Mùa hè là thời gian tuyệt vời để du lịch. VieTravel giới thiệu 5 điểm đến tuyệt vời mà bạn không nên bỏ qua. Từ những bãi biển xinh đẹp của Phú Quốc đến những dòng suối mát lạnh ở Sapa, mỗi nơi đều có những trải nghiệm độc đáo. Hãy chuẩn bị hành trang và khám phá Việt Nam đẹp lắm!',
                'category' => 'Mẹo du lịch',
                'image' => null,
            ],
            [
                'title' => 'Hà Nội 4 ngày - Khám phá thủ đô ngàn năm văn hiến',
                'description' => 'Tour du lịch Hà Nội 4 ngày với những điểm tham quan nổi tiếng',
                'content' => 'Hà Nội là thủ đô của Việt Nam với lịch sử và văn hóa phong phú. Tour này sẽ đưa bạn đến những địa điểm nổi tiếng như Hồ Hoàn Kiếm, Mausoleum Hồ Chí Minh, và phố cổ Hà Nội. Ngoài ra, bạn còn có cơ hội thưởng thức ẩm thực địa phương tuyệt vời.',
                'category' => 'Du lịch trong nước',
                'image' => null,
            ],
            [
                'title' => 'Tiêu chí chọn lựa tour du lịch tốt cho gia đình',
                'description' => 'Hướng dẫn chi tiết để chọn tour du lịch phù hợp cho cả gia đình',
                'content' => 'Chọn tour du lịch cho gia đình không phải dễ dàng. Bài viết này sẽ giúp bạn hiểu rõ hơn về các tiêu chí quan trọng khi lựa chọn tour. Từ giá cả, lịch trình, đến chất lượng dịch vụ, tất cả đều được xem xét cẩn thận. Hãy đọc bài viết để có một chuyến du lịch gia đình tuyệt vời.',
                'category' => 'Mẹo du lịch',
                'image' => null,
            ],
            [
                'title' => 'Du lịch Thái Lan - Trải nghiệm đất nước chùa vàng',
                'description' => 'Khám phá vẻ đẹp và nền văn hóa độc đáo của Thái Lan',
                'content' => 'Thái Lan là một trong những điểm đến phổ biến nhất ở châu Á. Với những ngôi chùa tráng lệ, ẩm thực ngon, và con người thân thiện, Thái Lan chắc chắn sẽ làm bạn ấn tượng. Tour du lịch Thái Lan của VieTravel sẽ mang đến cho bạn những trải nghiệm không bao giờ quên.',
                'category' => 'Du lịch ngoài nước',
                'image' => null,
            ],
            [
                'title' => 'Phú Quốc 3 ngày - Thiên đường du lịch biển',
                'description' => 'Hành trình khám phá hòn đảo đẹp nhất Việt Nam',
                'content' => 'Phú Quốc là hòn đảo lớn nhất Việt Nam với bãi biển đẹp tuyệt vời. Chuyến du lịch này sẽ đưa bạn tới những bãi biển xinh đẹp, thưởng thức hải sản tươi ngon, và trải nghiệm cuộc sống đảo thú vị. Đây là lựa chọn hoàn hảo cho một kỳ nghỉ thư giãn.',
                'category' => 'Du lịch trong nước',
                'image' => null,
            ],
            [
                'title' => 'Những lưu ý quan trọng khi du lịch quốc tế',
                'description' => 'Chuẩn bị chu đáo cho chuyến du lịch nước ngoài của bạn',
                'content' => 'Du lịch quốc tế đòi hỏi chuẩn bị chu đáo hơn du lịch trong nước. Hãy tìm hiểu về hộ chiếu, visa, bảo hiểm du lịch, và các quy định nhập cảnh. Bài viết này sẽ giúp bạn tránh những sai lầm thường gặp và có một chuyến du lịch suôn sẻ.',
                'category' => 'Mẹo du lịch',
                'image' => null,
            ],
            [
                'title' => 'Đà Nẵng - Thành phố của những cây cầu độc đáo',
                'description' => 'Khám phá vẻ đẹp hiện đại của thành phố Đà Nẵng',
                'content' => 'Đà Nẵng là thành phố du lịch phát triển nhất ở miền Trung Việt Nam. Với những cây cầu độc đáo, bãi biển Mỹ Khê sạch sẽ, và ẩm thực đặc sắc, Đà Nẵng sẽ mang đến cho bạn một trải nghiệm tuyệt vời. Hãy ghé thăm thành phố này trong chuyến du lịch tiếp theo.',
                'category' => 'Du lịch trong nước',
                'image' => null,
            ],
            [
                'title' => 'Nhật Bản mùa xuân - Hoa anh đào nở rộ',
                'description' => 'Thời điểm lý tưởng để ghé thăm Nhật Bản vào mùa xuân',
                'content' => 'Mùa xuân ở Nhật Bản là thời gian đẹp nhất trong năm khi hoa anh đào nở rộ. Ngoài các điểm tham quan nổi tiếng như Kyoto, Tokyo, Osaka, bạn còn có cơ hội ngắm hoa anh đào tuyệt đẹp. Tour du lịch Nhật Bản mùa xuân của VieTravel sẽ mang đến cho bạn những kỷ niệm đáng quý.',
                'category' => 'Du lịch ngoài nước',
                'image' => null,
            ],
        ];

        foreach ($newsArticles as $article) {
            News::create([
                'title' => $article['title'],
                'slug' => Str::slug($article['title']),
                'description' => $article['description'],
                'content' => $article['content'],
                'image' => $article['image'],
                'author_id' => $author?->id,
                'category' => $article['category'],
                'views' => rand(10, 500),
                'published_at' => now()->subDays(rand(0, 30)),
                'is_published' => true,
            ]);
        }
    }
}

