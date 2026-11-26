// Nội dung chính sách dựa trên kế hoạch bán hàng của trại; các điều kiện vận hành cần được xác nhận.
export const policies = [
  {
    id: 'giao-hang', title: 'Giao hàng và nhận hàng', icon: '↗',
    body: 'Phạm vi phục vụ, phí giao dự kiến, xác nhận đơn và kiểm tra hàng khi nhận.',
    sections: [
      ['Phạm vi phục vụ', 'Trại ưu tiên phục vụ tại Đắk Lắk. Đơn gần trại có thể được giao theo khung giờ đã thống nhất; đơn xa hơn có thể được gom theo tuyến. Những khu vực chưa đáp ứng điều kiện vận chuyển chỉ được nhận sau khi trại xác nhận khả năng giao và bảo quản phù hợp.'],
      ['Xác nhận đơn và phí giao', 'Sau khi nhận yêu cầu, trại liên hệ xác nhận sản phẩm, số lượng, đơn vị tính, giá, địa chỉ, phí giao và thời gian dự kiến. Website hiển thị tiền hàng khi đặt; phí giao và tổng thanh toán được trại thông báo sau theo địa chỉ, tuyến và điều kiện bảo quản để khách đồng ý trước khi thực hiện đơn.'],
      ['Thay đổi và nhận hàng', 'Nếu thiếu hàng hoặc cần thay đổi lịch, trại thông báo sớm và chỉ thay sản phẩm khi khách đồng ý. Khi nhận hàng, khách kiểm tra số lượng, bao bì và tình trạng bên ngoài. Nếu không thể nhận hàng, hai bên thống nhất phương án giao lại; chi phí phát sinh được thông báo trước.'],
    ],
  },
  {
    id: 'thanh-toan', title: 'Thanh toán', icon: '₫',
    body: 'Các phương thức thanh toán và xác nhận tổng tiền khi mua trực tiếp từ trại.',
    sections: [
      ['Đơn hàng D2C', 'Khách có thể chọn thanh toán khi nhận hàng (COD) nếu tuyến giao hàng hỗ trợ, hoặc chuyển khoản theo thông tin chính thức do trại cung cấp sau khi xác nhận đơn. Website hiện ghi nhận lựa chọn thanh toán; việc đối chiếu tiền chuyển khoản được thực hiện thủ công.'],
      ['Tổng tiền và xác nhận', 'Website hiển thị tiền hàng trước khi khách gửi đơn. Phí giao được xác định theo địa chỉ, tuyến và điều kiện bảo quản. Nhân viên thông báo phí giao và tổng tiền để khách đồng ý trước khi thực hiện đơn. Nếu có thay đổi, trại thông báo để khách đồng ý trước khi tiếp tục.'],
      ['An toàn giao dịch', 'Khi chuyển khoản, khách sử dụng thông tin nhận tiền chính thức được trại xác nhận và ghi nội dung theo hướng dẫn để đối chiếu đơn. Không cung cấp mật khẩu ngân hàng, mã OTP hoặc thông tin thẻ không cần thiết. Khoản đã thanh toán và việc hoàn tiền, nếu có, được đối chiếu theo đơn và thỏa thuận.'],
    ],
  },
  {
    id: 'doi-tra-den-bu', title: 'Đổi trả, hoàn tiền và đền bù', icon: '↺',
    body: 'Cách xử lý khi giao sai, thiếu hàng hoặc sản phẩm có dấu hiệu không bảo đảm chất lượng.',
    sections: [
      ['Trường hợp tiếp nhận', 'Khách có thể phản ánh khi giao sai loại, thiếu số lượng, bao bì hư hỏng, sản phẩm bất thường hoặc không đúng nội dung đã xác nhận. Cung cấp mã đơn, thời điểm nhận, mô tả và hình ảnh nếu có để hỗ trợ xác minh.'],
      ['Xác minh và phương án xử lý', 'Trại kiểm tra thông tin đơn và bằng chứng, sau đó thống nhất với khách phương án giao bù, đổi sản phẩm hoặc hoàn tiền phần hàng bị ảnh hưởng. Mức đền bù, nếu có, được xem xét theo nguyên nhân, mức độ ảnh hưởng, bằng chứng và quy định áp dụng.'],
      ['Sản phẩm thực phẩm', 'Nếu nghi ngờ thực phẩm không an toàn, khách cần ngừng sử dụng phần còn lại và thông báo sớm để trại phối hợp xác minh. Hàng giao đúng và bảo quản đúng hướng dẫn không được đổi trả chỉ vì khách thay đổi nhu cầu; điều kiện này không loại trừ trách nhiệm với hàng sai, hư hỏng hoặc không an toàn.'],
      ['Thời hạn và hoàn tiền', 'Thời gian tiếp nhận, xử lý và phương thức hoàn tiền cụ thể cần được trại xác nhận, công bố theo loại sản phẩm và thỏa thuận với khách. Không tự động xác nhận hoàn tiền qua website.'],
    ],
  },
  {
    id: 'bao-mat', title: 'Bảo mật dữ liệu cá nhân', icon: '⌑',
    body: 'Mục đích sử dụng dữ liệu, phạm vi tiếp cận và cách gửi yêu cầu hỗ trợ.',
    sections: [
      ['Thông tin và mục đích', 'Website tiếp nhận họ tên, số điện thoại, địa chỉ, nội dung đơn khi cần để xác nhận đơn, giao hàng, hỗ trợ và xử lý phản ánh. Khách chỉ cần cung cấp thông tin phù hợp với yêu cầu đang gửi.'],
      ['Quyền tiếp cận', 'Thông tin khách hàng không được công khai. Chỉ nhân sự liên quan đến bán hàng, giao hàng và hỗ trợ mới được tiếp cận theo công việc. Tên, thông tin liên hệ và địa chỉ trong các biểu mẫu được mã hóa khi lưu; mật khẩu tài khoản được băm.'],
      ['Quảng cáo và yêu cầu dữ liệu', 'Chỉ gửi thông tin quảng cáo hoặc ưu đãi khi khách đồng ý. Khách có thể gửi yêu cầu cập nhật thông tin, rút lại sự đồng ý hoặc hỏi về dữ liệu qua biểu mẫu phản ánh để được trại hỗ trợ. Thời hạn lưu và đầu mối xử lý cụ thể cần được trại công bố trước khi chính thức vận hành.'],
    ],
  },
  {
    id: 'ban-hang-khuyen-mai', title: 'Bán hàng và khuyến mãi', icon: '✳',
    body: 'Giá bán trực tiếp, ưu đãi và cách đặt hàng tại trại.',
    sections: [
      ['Giá bán và đặt hàng', 'Giá bán lẻ và đơn vị tính được hiển thị theo từng sản phẩm. Khách đăng nhập để thêm sản phẩm vào giỏ và gửi đơn; trại xác nhận sản phẩm, phí giao và tổng tiền trước khi thực hiện đơn.'],
      ['Thay đổi hoặc hủy đơn', 'Nếu cần thay đổi hoặc hủy đơn, khách liên hệ trại để kiểm tra tình trạng xử lý và thống nhất phương án. Website chưa hỗ trợ tự động hủy đơn; thời hạn và chi phí phát sinh, nếu có, được trại trao đổi trước với khách.'],
      ['Giá trị và lựa chọn', 'Trại hướng tới thông tin sản phẩm rõ ràng, phương án giao hàng phù hợp và hỗ trợ khi có phản ánh. Quy cách, giá và khả năng cung ứng cụ thể được xác nhận theo sản phẩm đang bán.'],
      ['Khuyến mãi', 'Khi có ưu đãi, trại công bố sản phẩm áp dụng, thời gian, giới hạn số lượng, điều kiện sử dụng và khả năng kết hợp với chương trình khác. Giá cuối cùng và phí giao được thông báo trước khi xác nhận đơn.'],
    ],
  },
  {
    id: 'khieu-nai', title: 'Tiếp nhận phản hồi và khiếu nại', icon: '✉',
    body: 'Gửi phản ánh, nhận mã tiếp nhận và phối hợp xác minh với trại.',
    sections: [
      ['Cách gửi phản ánh', 'Khách gửi biểu mẫu phản ánh trên website với tên, số điện thoại, mã đơn nếu có, thời điểm nhận và mô tả vấn đề. Sau khi gửi thành công, website cấp mã tiếp nhận. Ảnh chưa thể tải trực tiếp trên website; khách có thể bổ sung khi nhân viên liên hệ.'],
      ['Các bước giải quyết', 'Nhân viên kiểm tra đơn và thông tin khách cung cấp, liên hệ khi cần bổ sung bằng chứng, sau đó phản hồi phương án xử lý. Nếu chưa thống nhất, khách có thể yêu cầu chuyển phản ánh đến người phụ trách của trại. Kết quả xử lý cần được ghi nhận để cải thiện dịch vụ.'],
      ['Thông tin đang chờ xác nhận', 'Số điện thoại, địa chỉ liên hệ chính thức và thời gian phản hồi cụ thể cần được trại cung cấp trước khi công bố vận hành.'],
    ],
  },
  {
    id: 'chat-luong', title: 'Chất lượng và truy xuất nguồn gốc', icon: '⌕',
    body: 'Thông tin sản phẩm, bảo quản và hướng xử lý khi phát hiện vấn đề liên quan đến lô hàng.',
    sections: [
      ['Thông tin sản phẩm', 'Mỗi sản phẩm cần mô tả chính xác loại, quy cách, đơn vị, nguồn cung và tình trạng bảo quản. Ngày đóng gói, hạn sử dụng, cách bảo quản và chế biến cần được cung cấp khi áp dụng. Hình ảnh, giá và thông tin đang hiển thị trong bản demo cần được trại xác nhận.'],
      ['Theo dõi lô hàng', 'Quy trình vận hành cần lưu thông tin lô, ngày xuất hàng, tuyến giao và người nhận để hỗ trợ xác minh khi có phản ánh. Khi phát hiện vấn đề, trại cần tạm ngừng bán lô liên quan, xác định các đơn bị ảnh hưởng và liên hệ khách cần thiết.'],
      ['Phạm vi hiện tại', 'Chức năng quản lý và truy xuất lô hàng chưa được triển khai trên website. Trước khi đưa vào hoạt động thực tế, trại cần xác nhận quy trình lưu hồ sơ và cách xử lý lô hàng.'],
    ],
  },
];
